<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\GatewaySetting;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\User;
use App\Services\MisticPayService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Settings
        GatewaySetting::set('misticpay_base_url', 'https://api.misticpay.com');
        GatewaySetting::set('misticpay_public_key', '');
        GatewaySetting::set('misticpay_secret_key', '');
        GatewaySetting::set('misticpay_mock', 'true');

        // 2. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@asbank.com.br'],
            [
                'name' => 'Diretoria ASBANK',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'document' => '00.000.000/0001-99',
                'phone' => '(11) 98888-7777',
                'is_active' => true,
            ]
        );

        // 3. Client Users & Clients
        $clientUser1 = User::firstOrCreate(
            ['email' => 'carlos.silva@email.com'],
            [
                'name' => 'Carlos Eduardo Silva',
                'password' => Hash::make('cliente123'),
                'role' => 'client',
                'document' => '12345678901',
                'phone' => '11987654321',
                'is_active' => true,
            ]
        );

        $client1 = Client::firstOrCreate(
            ['email' => 'carlos.silva@email.com'],
            [
                'user_id' => $clientUser1->id,
                'name' => 'Carlos Eduardo Silva',
                'document' => '12345678901',
                'phone' => '11987654321',
                'birth_date' => '1988-04-15',
                'zip_code' => '01310-100',
                'address' => 'Avenida Paulista',
                'number' => '1578',
                'complement' => 'Apto 42',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'pix_key' => '12345678901',
                'pix_key_type' => 'cpf',
                'bank_name' => 'Nubank',
                'bank_agency' => '0001',
                'bank_account' => '1234567-8',
                'monthly_income' => 5500.00,
                'credit_limit' => 15000.00,
                'status' => 'ativo',
                'notes' => 'Cliente pontual com excelente score financeiro.',
            ]
        );

        $clientUser2 = User::firstOrCreate(
            ['email' => 'mariana.costa@email.com'],
            [
                'name' => 'Mariana Costa Santos',
                'password' => Hash::make('cliente123'),
                'role' => 'client',
                'document' => '98765432100',
                'phone' => '21998765432',
                'is_active' => true,
            ]
        );

        $client2 = Client::firstOrCreate(
            ['email' => 'mariana.costa@email.com'],
            [
                'user_id' => $clientUser2->id,
                'name' => 'Mariana Costa Santos',
                'document' => '98765432100',
                'phone' => '21998765432',
                'birth_date' => '1992-09-22',
                'zip_code' => '22041-001',
                'address' => 'Rua Barata Ribeiro',
                'number' => '450',
                'complement' => 'Bloco B',
                'neighborhood' => 'Copacabana',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'pix_key' => 'mariana.costa@email.com',
                'pix_key_type' => 'email',
                'bank_name' => 'Banco Inter',
                'bank_agency' => '0001',
                'bank_account' => '987654-3',
                'monthly_income' => 8200.00,
                'credit_limit' => 25000.00,
                'status' => 'ativo',
                'notes' => 'Profissional autônoma aprovada em análise de crédito automática.',
            ]
        );

        $client3 = Client::firstOrCreate(
            ['email' => 'roberto.mendes@email.com'],
            [
                'name' => 'Roberto Mendes Antunes',
                'document' => '45678912344',
                'phone' => '31976543210',
                'birth_date' => '1985-11-03',
                'zip_code' => '30140-071',
                'address' => 'Rua da Bahia',
                'number' => '1020',
                'neighborhood' => 'Lourdes',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'pix_key' => '31976543210',
                'pix_key_type' => 'phone',
                'bank_name' => 'Itaú Unibanco',
                'bank_agency' => '1234',
                'bank_account' => '54321-0',
                'monthly_income' => 4200.00,
                'credit_limit' => 10000.00,
                'status' => 'ativo',
            ]
        );

        // 4. Sample Loans
        // Loan 1: Carlos Eduardo - Ativo (PIX já enviado, 3 parcelas pagas de 6)
        $loan1 = Loan::firstOrCreate(
            ['contract_number' => 'ASB-2026-1001'],
            [
                'client_id' => $client1->id,
                'amount' => 5000.00,
                'interest_rate' => 2.50,
                'installments_count' => 6,
                'total_amount' => 5750.00, // 5000 + (5000 * 0.025 * 6) = 5750
                'installment_value' => 958.33,
                'first_due_date' => Carbon::now()->subMonths(2)->format('Y-m-d'),
                'status' => 'ativo',
                'credit_score' => 840,
                'credit_analysis_notes' => 'Score excelente. Limite pré-aprovado liberado sem avalista.',
                'pix_key' => $client1->pix_key,
                'pix_key_type' => $client1->pix_key_type,
                'disbursement_status' => 'enviado',
                'disbursement_transaction_id' => 'mp_out_20261101_8492',
                'disbursement_e2e' => 'E99999202611011030ASBANK5000',
                'disbursed_at' => Carbon::now()->subMonths(2),
                'created_by' => $admin->id,
                'contract_terms' => 'Instrumento particular de crédito mútuo ASBANK - Contrato ASB-2026-1001 devidamente assinado digitalmente.',
            ]
        );

        // Create installments for Loan 1
        for ($i = 1; $i <= 6; $i++) {
            $dueDate = Carbon::now()->subMonths(3 - $i)->startOfDay();
            $isPaid = $i <= 2;
            $installment = LoanInstallment::firstOrCreate(
                [
                    'loan_id' => $loan1->id,
                    'installment_number' => $i,
                ],
                [
                    'amount' => 958.33,
                    'due_date' => $dueDate->toDateString(),
                    'status' => $isPaid ? 'pago' : 'pendente',
                    'paid_at' => $isPaid ? $dueDate->copy()->addDays(1) : null,
                    'pix_transaction_id' => 'mp_ci_parcela_' . $i . '_' . rand(1000, 9999),
                    'pix_e2e' => $isPaid ? ('E' . rand(10000000, 99999999) . 'PAG' . $i) : null,
                ]
            );

            // Generate simulated QR Code for pending/unpaid
            if (!$isPaid && empty($installment->pix_qr_code)) {
                $pixCode = "00020126580014br.gov.bcb.pix0136ASBANK-PARC-{$loan1->id}-{$i}52040000530398654958.335802BR5920ASBANK SERVICOS FIN6009SAO PAULO62070503***6304ABCD";
                try {
                    $svg = QrCode::size(260)->generate($pixCode);
                    $installment->update([
                        'pix_qr_code' => $pixCode,
                        'pix_qr_code_base64' => 'data:image/svg+xml;base64,' . base64_encode($svg),
                        'pix_created_at' => now(),
                        'pix_expires_at' => now()->addDays(2),
                    ]);
                } catch (\Throwable $e) {}
            }
        }

        // Loan 2: Mariana Costa - Aprovado (Aguardando Envio do PIX pela diretoria)
        $loan2 = Loan::firstOrCreate(
            ['contract_number' => 'ASB-2026-1002'],
            [
                'client_id' => $client2->id,
                'amount' => 12000.00,
                'interest_rate' => 2.20,
                'installments_count' => 12,
                'total_amount' => 15168.00,
                'installment_value' => 1264.00,
                'first_due_date' => Carbon::now()->addMonth()->format('Y-m-d'),
                'status' => 'aprovado',
                'credit_score' => 895,
                'credit_analysis_notes' => 'Renda comprovada. Contrato aprovado pela mesa de crédito. Aguardando disparo do PIX.',
                'pix_key' => $client2->pix_key,
                'pix_key_type' => $client2->pix_key_type,
                'disbursement_status' => 'nao_enviado',
                'created_by' => $admin->id,
                'contract_terms' => 'Instrumento particular de crédito mútuo ASBANK - Contrato ASB-2026-1002 gerado automaticamente.',
            ]
        );

        for ($i = 1; $i <= 12; $i++) {
            $dueDate = Carbon::now()->addMonths($i)->startOfDay();
            LoanInstallment::firstOrCreate(
                [
                    'loan_id' => $loan2->id,
                    'installment_number' => $i,
                ],
                [
                    'amount' => 1264.00,
                    'due_date' => $dueDate->toDateString(),
                    'status' => 'pendente',
                ]
            );
        }

        // Loan 3: Roberto Mendes - Quitado
        $loan3 = Loan::firstOrCreate(
            ['contract_number' => 'ASB-2026-1003'],
            [
                'client_id' => $client3->id,
                'amount' => 3000.00,
                'interest_rate' => 2.80,
                'installments_count' => 3,
                'total_amount' => 3252.00,
                'installment_value' => 1084.00,
                'first_due_date' => Carbon::now()->subMonths(4)->format('Y-m-d'),
                'status' => 'quitado',
                'credit_score' => 790,
                'credit_analysis_notes' => 'Contrato 100% liquidado antecipadamente.',
                'pix_key' => $client3->pix_key,
                'pix_key_type' => $client3->pix_key_type,
                'disbursement_status' => 'enviado',
                'disbursement_transaction_id' => 'mp_out_20260715_1201',
                'disbursement_e2e' => 'E99999202607151030ASBANK3000',
                'disbursed_at' => Carbon::now()->subMonths(4),
                'created_by' => $admin->id,
            ]
        );

        for ($i = 1; $i <= 3; $i++) {
            $dueDate = Carbon::now()->subMonths(4 - $i)->startOfDay();
            LoanInstallment::firstOrCreate(
                [
                    'loan_id' => $loan3->id,
                    'installment_number' => $i,
                ],
                [
                    'amount' => 1084.00,
                    'due_date' => $dueDate->toDateString(),
                    'status' => 'pago',
                    'paid_at' => $dueDate->copy()->subDays(2),
                    'pix_transaction_id' => 'mp_ci_quitada_' . $i,
                    'pix_e2e' => 'E7777777777777777PAG' . $i,
                ]
            );
        }

        // 5. Seed Extrato (Financial Transactions: Envios e Recebimentos)
        // Cash-Out: Desembolso Carlos
        FinancialTransaction::firstOrCreate(
            ['misticpay_transaction_id' => 'mp_out_20261101_8492'],
            [
                'client_id' => $client1->id,
                'loan_id' => $loan1->id,
                'type' => 'cash_out',
                'category' => 'Desembolso de Empréstimo',
                'amount' => 5000.00,
                'fee' => 0.00,
                'e2e' => 'E99999202611011030ASBANK5000',
                'pix_key' => $client1->pix_key,
                'pix_key_type' => $client1->pix_key_type,
                'counterparty_name' => $client1->name,
                'counterparty_document' => $client1->document,
                'status' => 'completo',
                'description' => "Envio de PIX para liberação de empréstimo Contrato {$loan1->contract_number}",
                'created_at' => Carbon::now()->subMonths(2),
            ]
        );

        // Cash-In: Parcela 1 Carlos
        FinancialTransaction::firstOrCreate(
            ['misticpay_transaction_id' => 'mp_ci_parcela_1_paid'],
            [
                'client_id' => $client1->id,
                'loan_id' => $loan1->id,
                'type' => 'cash_in',
                'category' => 'Pagamento de Parcela PIX',
                'amount' => 958.33,
                'fee' => 1.50,
                'e2e' => 'E1234567890123456789PAG1',
                'counterparty_name' => $client1->name,
                'counterparty_document' => $client1->document,
                'status' => 'completo',
                'description' => "Recebimento Parcela 1/6 - Contrato {$loan1->contract_number}",
                'created_at' => Carbon::now()->subMonths(2)->addDays(1),
            ]
        );

        // Cash-In: Parcela 2 Carlos
        FinancialTransaction::firstOrCreate(
            ['misticpay_transaction_id' => 'mp_ci_parcela_2_paid'],
            [
                'client_id' => $client1->id,
                'loan_id' => $loan1->id,
                'type' => 'cash_in',
                'category' => 'Pagamento de Parcela PIX',
                'amount' => 958.33,
                'fee' => 1.50,
                'e2e' => 'E1234567890123456789PAG2',
                'counterparty_name' => $client1->name,
                'counterparty_document' => $client1->document,
                'status' => 'completo',
                'description' => "Recebimento Parcela 2/6 - Contrato {$loan1->contract_number}",
                'created_at' => Carbon::now()->subMonth()->addDays(2),
            ]
        );

        // Cash-Out: Desembolso Roberto
        FinancialTransaction::firstOrCreate(
            ['misticpay_transaction_id' => 'mp_out_20260715_1201'],
            [
                'client_id' => $client3->id,
                'loan_id' => $loan3->id,
                'type' => 'cash_out',
                'category' => 'Desembolso de Empréstimo',
                'amount' => 3000.00,
                'fee' => 0.00,
                'e2e' => 'E99999202607151030ASBANK3000',
                'pix_key' => $client3->pix_key,
                'pix_key_type' => $client3->pix_key_type,
                'counterparty_name' => $client3->name,
                'counterparty_document' => $client3->document,
                'status' => 'completo',
                'description' => "Envio de PIX para liberação de empréstimo Contrato {$loan3->contract_number}",
                'created_at' => Carbon::now()->subMonths(4),
            ]
        );
    }
}
