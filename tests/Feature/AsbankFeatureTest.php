<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\User;
use App\Services\MisticPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AsbankFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_landing_page_is_accessible()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('ASBANK');
        $response->assertSee('Confiança que cresce');
    }

    public function test_admin_can_access_admin_dashboard_and_has_left_menu()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Financeiro');
        $response->assertSee('Clientes');
        $response->assertSee('Empréstimos');
        $response->assertSee('Extrato Geral');
        $response->assertSee('Ver Área do Cliente');
        $response->assertDontSee('admin/configuracoes/misticpay');
    }

    public function test_client_cannot_access_admin_area()
    {
        $clientUser = User::where('role', 'client')->first();
        $response = $this->actingAs($clientUser)->get('/admin');

        $response->assertRedirect('/cliente');
    }

    public function test_admin_can_create_a_client()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post('/admin/clients', [
            'name' => 'Ana Beatriz Nogueira',
            'email' => 'ana.nogueira@email.com',
            'document' => '11122233344',
            'phone' => '11988889999',
            'pix_key' => '11122233344',
            'pix_key_type' => 'cpf',
            'create_user_account' => 1,
            'password' => 'asbank123',
        ]);

        $this->assertDatabaseHas('clients', [
            'email' => 'ana.nogueira@email.com',
            'document' => '11122233344',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'ana.nogueira@email.com',
            'role' => 'client',
        ]);
    }

    public function test_admin_can_create_loan_and_generates_installments()
    {
        $admin = User::where('role', 'admin')->first();
        $client = Client::first();

        $response = $this->actingAs($admin)->post('/admin/loans', [
            'client_id' => $client->id,
            'amount' => 4000.00,
            'interest_rate' => 2.50,
            'installments_count' => 4,
            'first_due_date' => now()->addMonth()->format('Y-m-d'),
            'pix_key' => $client->pix_key,
            'pix_key_type' => $client->pix_key_type,
        ]);

        $loan = Loan::where('client_id', $client->id)->where('amount', 4000.00)->first();
        $this->assertNotNull($loan);
        $this->assertEquals(4, $loan->installments()->count());
        $this->assertStringStartsWith('ASB-', $loan->contract_number);
    }

    public function test_admin_can_send_pix_disbursement()
    {
        $admin = User::where('role', 'admin')->first();
        $loan = Loan::where('disbursement_status', 'nao_enviado')->first();

        $response = $this->actingAs($admin)->post("/admin/loans/{$loan->id}/enviar-pix");

        $loan->refresh();
        $this->assertEquals('enviado', $loan->disbursement_status);
        $this->assertNotNull($loan->disbursement_transaction_id);

        $this->assertDatabaseHas('financial_transactions', [
            'loan_id' => $loan->id,
            'type' => 'cash_out',
        ]);
    }

    public function test_admin_can_generate_installment_pix_charge()
    {
        $admin = User::where('role', 'admin')->first();
        $installment = LoanInstallment::where('status', 'pendente')->whereNull('pix_qr_code')->first();

        if (!$installment) {
            $installment = LoanInstallment::where('status', 'pendente')->first();
            $installment->update(['pix_qr_code' => null]);
        }

        $response = $this->actingAs($admin)->post("/admin/installments/{$installment->id}/gerar-pix");

        $installment->refresh();
        $this->assertNotNull($installment->pix_qr_code);
        $this->assertNotNull($installment->pix_transaction_id);
    }

    public function test_statement_shows_cash_in_and_cash_out()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/extrato');
        $response->assertStatus(200);
        $response->assertSee('Total Enviado (Cash-Out)');
        $response->assertSee('Total Recebido (Cash-In)');
    }

    public function test_client_portal_dashboard_shows_contracts_and_installments()
    {
        $clientUser = User::where('email', 'carlos.silva@email.com')->first();

        $response = $this->actingAs($clientUser)->get('/cliente');
        $response->assertStatus(200);
        $response->assertSee('Carlos');
        $response->assertSee('ASB-2026-1001');
    }

    public function test_misticpay_webhook_handles_pix_deposit_payment()
    {
        $installment = LoanInstallment::where('status', 'pendente')->first();
        $simTxId = 'mp_webhook_test_' . rand(1000, 9999);
        $installment->update(['pix_transaction_id' => $simTxId]);

        $payload = [
            'transactionId' => $simTxId,
            'transactionType' => 'DEPOSITO',
            'status' => 'COMPLETO',
            'value' => (float) $installment->amount,
            'e2e' => 'E111222333444555666777',
            'clientName' => 'Carlos Eduardo Silva',
            'clientDocument' => '12345678901',
        ];

        $response = $this->postJson('/api/webhooks/misticpay', $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $installment->refresh();
        $this->assertEquals('pago', $installment->status);
        $this->assertEquals('E111222333444555666777', $installment->pix_e2e);

        $this->assertDatabaseHas('financial_transactions', [
            'installment_id' => $installment->id,
            'type' => 'cash_in',
            'status' => 'completo',
        ]);
    }

    public function test_artisan_misticpay_test_command_runs_successfully()
    {
        $this->artisan('misticpay:test')
            ->expectsOutputToContain('ASBANK • Diagnóstico & Teste da API MisticPay PIX')
            ->assertExitCode(0);
    }

    public function test_admin_can_create_loan_with_auto_send_pix()
    {
        $admin = User::where('role', 'admin')->first();
        $client = Client::first();

        $this->actingAs($admin)->post('/admin/loans', [
            'client_id' => $client->id,
            'amount' => 150.00,
            'interest_rate' => 0,
            'installments_count' => 1,
            'first_due_date' => now()->addMonth()->format('Y-m-d'),
            'pix_key' => $client->pix_key,
            'pix_key_type' => $client->pix_key_type,
            'auto_send_pix' => 1,
        ]);

        $loan = Loan::where('client_id', $client->id)->where('amount', 150.00)->first();
        $this->assertNotNull($loan);
        $this->assertEquals('enviado', $loan->disbursement_status);
        $this->assertEquals('ativo', $loan->status);
        $this->assertDatabaseHas('financial_transactions', [
            'loan_id' => $loan->id,
            'type' => 'cash_out',
            'status' => 'completo',
        ]);
    }

    public function test_misticpay_create_pix_charge_parses_data_and_copy_paste_correctly()
    {
        Config::set('services.misticpay.public_key', 'pk_test_123');
        Config::set('services.misticpay.secret_key', 'sk_test_456');
        Config::set('services.misticpay.mock', false);

        Http::fake([
            'https://api.misticpay.com/api/transactions/create' => Http::response([
                'message' => 'Transação criada com sucesso',
                'data' => [
                    'transactionId' => '31484480',
                    'transactionState' => 'PENDENTE',
                    'qrCodeBase64' => 'data:image/png;base64,fakebase64',
                    'copyPaste' => '00020101021226820014br.gov.bcb.pix2560qrcode',
                ],
            ], 200),
        ]);

        $service = new MisticPayService();
        $result = $service->createPixCharge(10.00, 'Adenilson Silva', '615.654.413-54', 'Parcela 1/1', 'https://asbank.test/api/webhooks/misticpay', 1);

        $this->assertTrue($result['success']);
        $this->assertEquals('31484480', $result['transaction_id']);
        $this->assertEquals('00020101021226820014br.gov.bcb.pix2560qrcode', $result['qr_code']);
        $this->assertEquals('data:image/png;base64,fakebase64', $result['qr_code_base64']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.misticpay.com/api/transactions/create'
                && !empty($request['transactionId'])
                && $request['payerDocument'] === '61565441354'
                && $request['projectWebhook'] === 'https://asbank.test/api/webhooks/misticpay';
        });
    }

    public function test_misticpay_withdraw_queued_response_and_phone_key_mapping()
    {
        Config::set('services.misticpay.public_key', 'pk_test_123');
        Config::set('services.misticpay.secret_key', 'sk_test_456');
        Config::set('services.misticpay.mock', false);

        Http::fake([
            'https://api.misticpay.com/api/transactions/withdraw' => Http::response([
                'message' => 'Saque adicionado à fila de processamento',
                'data' => [
                    'jobId' => 'withdraw-4-job',
                    'transactionId' => 54345,
                    'status' => 'QUEUED',
                ],
            ], 200),
        ]);

        $admin = User::where('role', 'admin')->first();
        $loan = Loan::where('disbursement_status', 'nao_enviado')->first();
        $loan->update(['pix_key' => '(11) 98765-4321', 'pix_key_type' => 'phone']);

        $this->actingAs($admin)->post("/admin/loans/{$loan->id}/enviar-pix");

        Http::assertSent(function ($request) {
            return $request['pixKey'] === '11987654321'
                && $request['pixKeyType'] === 'TELEFONE';
        });

        $loan->refresh();
        $this->assertEquals('processando', $loan->disbursement_status);
        $this->assertEquals('54345', $loan->disbursement_transaction_id);

        // Now webhook arrives for 54345
        $this->postJson('/api/webhooks/misticpay', [
            'transactionId' => 54345,
            'transactionType' => 'RETIRADA',
            'status' => 'COMPLETO',
            'e2e' => 'E999888777666',
        ])->assertStatus(200);

        $loan->refresh();
        $this->assertEquals('enviado', $loan->disbursement_status);
        $this->assertEquals('E999888777666', $loan->disbursement_e2e);
    }

    public function test_misticpay_check_transaction_syncs_status()
    {
        Config::set('services.misticpay.public_key', 'pk_test_123');
        Config::set('services.misticpay.secret_key', 'sk_test_456');
        Config::set('services.misticpay.mock', false);

        $installment = LoanInstallment::where('status', 'pendente')->first();
        $installment->update(['pix_transaction_id' => '987654321']);

        Http::fake([
            'https://api.misticpay.com/api/transactions/check' => Http::response([
                'message' => 'Transação encontrada com sucesso!',
                'transaction' => [
                    'transactionId' => '987654321',
                    'value' => (float) $installment->amount,
                    'fee' => 0.35,
                    'transactionState' => 'COMPLETO',
                    'transactionType' => 'DEPOSITO',
                    'e2e' => 'E1234567890SYNC',
                ],
            ], 200),
        ]);

        $service = new MisticPayService();
        $check = $service->syncTransactionStatus('987654321');

        $this->assertTrue($check['success']);
        $this->assertEquals('COMPLETO', $check['status']);

        $installment->refresh();
        $this->assertEquals('pago', $installment->status);
        $this->assertEquals('E1234567890SYNC', $installment->pix_e2e);
    }
}
