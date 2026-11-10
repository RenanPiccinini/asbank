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
        $response->assertSee('Desenvolvido por');
        $response->assertSee('https://piccininidigitalsolutions.com.br/');
        $response->assertSee('pds-logo.png');
        $response->assertSee('solicitar-emprestimo');
        $response->assertDontSee('admin123');
        $response->assertDontSee('cliente123');
        $response->assertDontSee('Ambientes Prontos');
    }

    public function test_admin_can_access_admin_dashboard_and_has_left_menu()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Financeiro');
        $response->assertSee('Análises de Crédito');
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

    public function test_public_loan_application_prefills_simulation_and_creates_pending_blocked_account()
    {
        $getResponse = $this->get('/solicitar-emprestimo?amount=12000&installments=24');
        $getResponse->assertStatus(200);
        $getResponse->assertSee('12000.00');
        $getResponse->assertSee('value="24"', false);

        $postResponse = $this->post('/solicitar-emprestimo', [
            'amount' => 8000,
            'installments_count' => 10,
            'loan_purpose' => 'Capital de Giro / Negócios',
            'name' => 'Mariana Costa Oliveira',
            'document' => '456.789.123-00',
            'rg' => '12345678 SSP-SP',
            'birth_date' => '1992-05-14',
            'email' => 'mariana.costa@example.com',
            'phone' => '(11) 98765-4321',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'monthly_income' => 6500,
            'occupation' => 'Comerciante',
            'zip_code' => '01310-100',
            'address' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => 'Conjunto 42',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'sp',
            'bank_name' => 'Nubank',
            'bank_agency' => '0001',
            'bank_account' => '998877-6',
            'pix_key_type' => 'cpf',
            'pix_key' => '45678912300',
            'accept_terms' => '1',
        ]);

        $postResponse->assertRedirect('/solicitar-emprestimo/enviado');

        $successPage = $this->get('/solicitar-emprestimo/enviado');
        $successPage->assertStatus(200);
        $successPage->assertSee('Solicitação enviada com sucesso!');
        $successPage->assertSee('Mariana Costa Oliveira');

        $user = User::where('email', 'mariana.costa@example.com')->firstOrFail();
        $this->assertFalse($user->is_active);

        $client = Client::where('user_id', $user->id)->firstOrFail();
        $this->assertEquals('pendente', $client->status);
        $this->assertEquals('Comerciante', $client->occupation);

        $loan = Loan::where('client_id', $client->id)->firstOrFail();
        $this->assertEquals('em_analise', $loan->status);
        $this->assertEquals('site', $loan->origin);
        $this->assertCount(0, $loan->installments);

        // Login must be blocked while account is pending
        $loginAttempt = $this->post('/login', [
            'email' => 'mariana.costa@example.com',
            'password' => 'secret123',
        ]);
        $loginAttempt->assertSessionHasErrors('email');
        $this->assertGuest();

        // Direct portal route access must also block inactive user
        $this->actingAs($user)->get('/cliente')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_admin_can_edit_proposal_and_approve_unlocking_portal_and_whatsapp()
    {
        $this->post('/solicitar-emprestimo', [
            'amount' => 5000,
            'installments_count' => 12,
            'loan_purpose' => 'Pessoal / Emergência',
            'name' => 'Roberto Almeida',
            'document' => '321.654.987-00',
            'birth_date' => '1988-09-20',
            'email' => 'roberto.almeida@example.com',
            'phone' => '(21) 99888-7766',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
            'monthly_income' => 5000,
            'occupation' => 'Analista de Sistemas',
            'zip_code' => '20040-020',
            'address' => 'Rua da Assembleia',
            'number' => '10',
            'neighborhood' => 'Centro',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
            'bank_name' => 'Banco Inter',
            'pix_key_type' => 'phone',
            'pix_key' => '21998887766',
            'accept_terms' => '1',
        ])->assertRedirect('/solicitar-emprestimo/enviado');

        $admin = User::where('role', 'admin')->firstOrFail();
        $loan = Loan::where('origin', 'site')->firstOrFail();

        // Admin sees pending queue and detail page with dynamic WhatsApp link
        $this->actingAs($admin)->get('/admin/analises')
            ->assertStatus(200)
            ->assertSee('Roberto Almeida')
            ->assertSee($loan->contract_number);

        $this->actingAs($admin)->get('/admin/analises/' . $loan->id)
            ->assertStatus(200)
            ->assertSee('https://wa.me/5521998887766')
            ->assertSee('Aprovar Proposta');

        // Admin edits counter-proposal and prepares WhatsApp message
        $editResponse = $this->actingAs($admin)->patch('/admin/analises/' . $loan->id . '/proposta', [
            'amount' => 4000,
            'interest_rate' => 2.00,
            'installments_count' => 6,
            'first_due_date' => now()->addMonth()->toDateString(),
            'credit_score' => 810,
            'credit_analysis_notes' => 'Contraproposta ajustada com taxa reduzida.',
            'pix_key_type' => 'phone',
            'pix_key' => '21998887766',
            'action' => 'save_and_whatsapp',
        ]);
        $editResponse->assertSessionHas('open_whatsapp_url');

        $loan->refresh();
        $this->assertEquals('4000.00', $loan->amount);
        $this->assertEquals(6, $loan->installments_count);

        // Admin approves proposal -> generates installments, contract terms, activates user/client
        $this->actingAs($admin)->post('/admin/analises/' . $loan->id . '/aprovar')
            ->assertSessionHas('success');

        $loan->refresh();
        $client = $loan->client->fresh();
        $user = $client->user->fresh();

        $this->assertEquals('aprovado', $loan->status);
        $this->assertEquals('ativo', $client->status);
        $this->assertTrue($user->is_active);
        $this->assertCount(6, $loan->installments);
        $this->assertStringNotContainsString('number_format', (string) $loan->contract_terms);

        // After approval, Approve button is gone and WhatsApp Approval communication button is enabled
        $approvedShow = $this->actingAs($admin)->get('/admin/analises/' . $loan->id);
        $approvedShow->assertStatus(200);
        $approvedShow->assertDontSee('Aprovar Proposta & Liberar Acesso');
        $approvedShow->assertSee('Comunicar Aprovação por WhatsApp');
        $approvedShow->assertSee('https://wa.me/5521998887766');

        // Client can now sign in and access their dashboard
        auth()->logout();
        $this->post('/login', [
            'email' => 'roberto.almeida@example.com',
            'password' => 'senha123',
        ])->assertRedirect('/dashboard');

        $this->actingAs($user)->get('/cliente')
            ->assertStatus(200)
            ->assertSee($loan->contract_number);
    }

    public function test_admin_can_reject_and_reopen_proposal_keeping_user_blocked()
    {
        $this->post('/solicitar-emprestimo', [
            'amount' => 15000,
            'installments_count' => 12,
            'loan_purpose' => 'Quitação de Dívidas',
            'name' => 'Fernanda Lima Dias',
            'document' => '111.222.333-44',
            'birth_date' => '1995-03-10',
            'email' => 'fernanda.dias@example.com',
            'phone' => '(31) 99123-4567',
            'password' => 'fernanda123',
            'password_confirmation' => 'fernanda123',
            'monthly_income' => 2000,
            'occupation' => 'Vendedora',
            'zip_code' => '30130-000',
            'address' => 'Avenida Afonso Pena',
            'number' => '500',
            'neighborhood' => 'Centro',
            'city' => 'Belo Horizonte',
            'state' => 'MG',
            'bank_name' => 'Itaú',
            'pix_key_type' => 'email',
            'pix_key' => 'fernanda.dias@example.com',
            'accept_terms' => '1',
        ])->assertRedirect('/solicitar-emprestimo/enviado');

        $admin = User::where('role', 'admin')->firstOrFail();
        $loan = Loan::whereHas('client', fn ($q) => $q->where('email', 'fernanda.dias@example.com'))->firstOrFail();

        $this->actingAs($admin)->post('/admin/analises/' . $loan->id . '/reprovar', [
            'rejection_reason' => 'Comprometimento de renda acima do limite.',
        ])->assertSessionHas('info');

        $loan->refresh();
        $this->assertEquals('rejeitado', $loan->status);
        $this->assertEquals('rejeitado', $loan->client->status);
        $this->assertFalse($loan->client->user->is_active);

        auth()->logout();
        $this->post('/login', [
            'email' => 'fernanda.dias@example.com',
            'password' => 'fernanda123',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($admin)->post('/admin/analises/' . $loan->id . '/reabrir')
            ->assertSessionHas('success');

        $loan->refresh();
        $this->assertEquals('em_analise', $loan->status);
        $this->assertEquals('pendente', $loan->client->status);
        $this->assertFalse($loan->client->user->is_active);
    }
}
