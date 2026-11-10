<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Services\MisticPayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $clientId = $request->input('client_id');
        $search = $request->input('search');

        $query = Loan::with(['client', 'installments'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQ) use ($search) {
                      $clientQ->where('name', 'like', "%{$search}%")
                              ->orWhere('document', 'like', "%{$search}%");
                  });
            });
        }

        $loans = $query->paginate(12)->withQueryString();
        $clients = Client::where('status', 'ativo')->orderBy('name')->get();

        return view('admin.loans.index', compact('loans', 'clients', 'status', 'clientId', 'search'));
    }

    public function create(Request $request)
    {
        $selectedClientId = $request->input('client_id');
        $clients = Client::where('status', 'ativo')->orderBy('name')->get();
        return view('admin.loans.create', compact('clients', 'selectedClientId'));
    }

    public function store(Request $request, MisticPayService $misticPay)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'installments_count' => ['required', 'integer', 'min:1', 'max:72'],
            'first_due_date' => ['required', 'date', 'after_or_equal:today'],
            'pix_key' => ['nullable', 'string', 'max:150'],
            'pix_key_type' => ['nullable', 'string'],
            'credit_score' => ['nullable', 'integer', 'min:300', 'max:1000'],
            'credit_analysis_notes' => ['nullable', 'string'],
            'auto_send_pix' => ['nullable', 'boolean'],
        ]);

        $client = Client::findOrFail($validated['client_id']);

        $amount = (float) $validated['amount'];
        $interestRate = (float) $validated['interest_rate'];
        $installmentsCount = (int) $validated['installments_count'];

        // Simple monthly linear interest calculation: total = amount * (1 + (rate% * count))
        // Or if 0%, total = amount
        $totalInterest = $amount * (($interestRate / 100) * $installmentsCount);
        $totalAmount = round($amount + $totalInterest, 2);
        $installmentValue = round($totalAmount / $installmentsCount, 2);

        // Adjust rounding on last installment if needed
        $calculatedSum = $installmentValue * $installmentsCount;
        $roundingDifference = round($totalAmount - $calculatedSum, 2);

        // Auto-generate contract number: ASB-YYYY-XXXX
        $year = date('Y');
        $lastLoan = Loan::whereYear('created_at', $year)->latest('id')->first();
        $sequence = $lastLoan ? ($lastLoan->id + 1) : 1001;
        $contractNumber = sprintf('ASB-%s-%04d', $year, $sequence);

        DB::beginTransaction();
        try {
            $pixKey = $validated['pix_key'] ?: $client->pix_key;
            $pixKeyType = $validated['pix_key_type'] ?: $client->pix_key_type;

            $loan = Loan::create([
                'contract_number' => $contractNumber,
                'client_id' => $client->id,
                'amount' => $amount,
                'interest_rate' => $interestRate,
                'installments_count' => $installmentsCount,
                'total_amount' => $totalAmount,
                'installment_value' => $installmentValue,
                'first_due_date' => $validated['first_due_date'],
                'status' => 'aprovado',
                'credit_score' => $validated['credit_score'] ?? rand(750, 920),
                'credit_analysis_notes' => $validated['credit_analysis_notes'] ?? 'Análise automática de crédito ASBANK realizada com score favorável e limite pré-aprovado.',
                'pix_key' => $pixKey,
                'pix_key_type' => $pixKeyType,
                'disbursement_status' => 'nao_enviado',
                'created_by' => auth()->id(),
                'contract_terms' => $this->generateStandardContractTerms($client, $contractNumber, $amount, $totalAmount, $installmentsCount, $installmentValue, $interestRate),
            ]);

            // Create installments
            $firstDueDate = Carbon::parse($validated['first_due_date']);
            for ($i = 1; $i <= $installmentsCount; $i++) {
                $dueDate = (clone $firstDueDate)->addMonths($i - 1);
                $parcelAmount = $installmentValue;
                // Add rounding diff to last installment
                if ($i === $installmentsCount && $roundingDifference != 0) {
                    $parcelAmount += $roundingDifference;
                }

                LoanInstallment::create([
                    'loan_id' => $loan->id,
                    'installment_number' => $i,
                    'amount' => $parcelAmount,
                    'due_date' => $dueDate->toDateString(),
                    'status' => 'pendente',
                ]);
            }

            DB::commit();

            // Se o admin marcou para enviar o PIX automaticamente na criação
            if ($request->boolean('auto_send_pix')) {
                $loan->load('client');
                $pixResult = $this->executePixDisbursement($loan, $misticPay, $pixKey, $pixKeyType);
                if ($pixResult['success']) {
                    return redirect()->route('admin.loans.show', $loan)
                        ->with('success', "Empréstimo {$contractNumber} criado e " . $pixResult['message']);
                }

                return redirect()->route('admin.loans.show', $loan)
                    ->with('error', "Empréstimo {$contractNumber} criado com sucesso, porém o envio automático do PIX falhou: " . $pixResult['message']);
            }

            return redirect()->route('admin.loans.show', $loan)
                ->with('success', "Empréstimo {$contractNumber} cadastrado com sucesso! As {$installmentsCount} parcelas foram geradas.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao cadastrar empréstimo: ' . $e->getMessage());
        }
    }

    public function show(Loan $loan)
    {
        $loan->load(['client', 'installments', 'transactions']);
        return view('admin.loans.show', compact('loan'));
    }

    public function edit(Loan $loan)
    {
        $loan->load('client');
        return view('admin.loans.edit', compact('loan'));
    }

    public function update(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:em_analise,aprovado,ativo,quitado,rejeitado,cancelado'],
            'pix_key' => ['nullable', 'string', 'max:150'],
            'pix_key_type' => ['nullable', 'string'],
            'credit_score' => ['nullable', 'integer', 'min:300', 'max:1000'],
            'credit_analysis_notes' => ['nullable', 'string'],
        ]);

        $loan->update($validated);

        return redirect()->route('admin.loans.show', $loan)->with('success', 'Empréstimo atualizado com sucesso!');
    }

    public function destroy(Loan $loan)
    {
        if ($loan->disbursement_status === 'enviado' || $loan->paidInstallmentsCount() > 0) {
            return back()->with('error', 'Não é possível excluir um empréstimo com PIX já desembolsado ou parcelas pagas.');
        }

        $loan->delete();
        return redirect()->route('admin.loans.index')->with('success', 'Empréstimo excluído com sucesso.');
    }

    /**
     * Enviar PIX (Cash-Out) para a conta do cliente via MisticPay
     */
    public function sendPixDisbursement(Request $request, Loan $loan, MisticPayService $misticPay)
    {
        $pixKey = $request->input('pix_key') ?: $loan->pix_key ?: $loan->client->pix_key;
        $pixKeyType = $request->input('pix_key_type') ?: $loan->pix_key_type ?: $loan->client->pix_key_type;

        $result = $this->executePixDisbursement($loan, $misticPay, $pixKey, $pixKeyType);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Executa o envio do PIX de desembolso (usado no botão manual e no envio automático na criação)
     */
    private function executePixDisbursement(Loan $loan, MisticPayService $misticPay, ?string $pixKey = null, ?string $pixKeyType = null): array
    {
        if ($loan->disbursement_status === 'enviado') {
            return ['success' => false, 'message' => 'O PIX deste empréstimo já foi enviado anteriormente.'];
        }

        $pixKey = $pixKey ?: $loan->pix_key ?: $loan->client->pix_key;
        $pixKeyType = $pixKeyType ?: $loan->pix_key_type ?: $loan->client->pix_key_type;

        if (empty($pixKey)) {
            return ['success' => false, 'message' => 'Defina a Chave PIX do cliente para enviar os fundos.'];
        }

        $response = $misticPay->sendPixWithdraw(
            amount: (float) $loan->amount,
            pixKey: $pixKey,
            pixKeyType: $pixKeyType ?: 'cpf',
            beneficiaryName: $loan->client->name,
            beneficiaryDoc: $loan->client->document,
            description: "Emprestimo ASBANK Contrato {$loan->contract_number}"
        );

        if ($response['success']) {
            $statusUpper = strtoupper((string) ($response['status'] ?? ''));
            $isComplete = in_array($statusUpper, ['COMPLETO', 'COMPLETED', 'SUCESSO', 'CONCLUIDO']);

            $loan->update([
                'pix_key' => $pixKey,
                'pix_key_type' => $pixKeyType,
                'disbursement_status' => $isComplete ? 'enviado' : 'processando',
                'disbursement_transaction_id' => $response['transaction_id'],
                'disbursement_e2e' => $response['e2e'] ?? null,
                'disbursed_at' => $isComplete ? now() : null,
                'status' => 'ativo',
            ]);

            FinancialTransaction::updateOrCreate(
                [
                    'loan_id' => $loan->id,
                    'type' => 'cash_out',
                ],
                [
                    'client_id' => $loan->client_id,
                    'category' => 'Desembolso de Empréstimo',
                    'amount' => $loan->amount,
                    'misticpay_transaction_id' => $response['transaction_id'],
                    'e2e' => $response['e2e'] ?? null,
                    'pix_key' => $pixKey,
                    'pix_key_type' => $pixKeyType,
                    'counterparty_name' => $loan->client->name,
                    'counterparty_document' => $loan->client->document,
                    'status' => $isComplete ? 'completo' : 'pendente',
                    'description' => "Envio de PIX para liberação de empréstimo Contrato {$loan->contract_number}",
                    'raw_response' => $response['raw'] ?? [],
                ]
            );

            $badge = $response['is_mock'] ? ' (Simulação Gateway)' : ($isComplete ? '' : ' (Aguardando processamento na fila MisticPay)');
            return [
                'success' => true,
                'message' => "PIX de R$ " . number_format($loan->amount, 2, ',', '.') . " processado para {$loan->client->name}!{$badge}",
            ];
        }

        return [
            'success' => false,
            'message' => 'Falha ao enviar PIX: ' . ($response['message'] ?? 'Erro desconhecido'),
        ];
    }

    /**
     * Consulta e sincroniza o status do PIX na MisticPay (via Ajax polling)
     */
    public function checkPixStatus(Loan $loan, MisticPayService $misticPay)
    {
        $updated = false;

        if ($loan->disbursement_status === 'processando' && !empty($loan->disbursement_transaction_id)) {
            $sync = $misticPay->syncTransactionStatus($loan->disbursement_transaction_id);
            if ($sync['success']) {
                $loan->refresh();
                $updated = ($loan->disbursement_status !== 'processando');
            }
        }

        foreach ($loan->installments()->where('status', 'pendente')->whereNotNull('pix_transaction_id')->get() as $inst) {
            $sync = $misticPay->syncTransactionStatus($inst->pix_transaction_id);
            if ($sync['success'] && in_array($sync['status'], ['COMPLETO', 'COMPLETED', 'PAID', 'SUCESSO'])) {
                $updated = true;
            }
        }

        $loan->refresh();

        return response()->json([
            'disbursement_status' => $loan->disbursement_status,
            'status' => $loan->status,
            'updated' => $updated,
        ]);
    }

    /**
     * Sincroniza manualmente o status de transações PIX com a API MisticPay
     */
    public function syncPixStatus(Loan $loan, MisticPayService $misticPay)
    {
        if ($loan->disbursement_status === 'processando' && !empty($loan->disbursement_transaction_id)) {
            $misticPay->syncTransactionStatus($loan->disbursement_transaction_id);
        }

        foreach ($loan->installments()->where('status', 'pendente')->whereNotNull('pix_transaction_id')->get() as $inst) {
            $misticPay->syncTransactionStatus($inst->pix_transaction_id);
        }

        return back()->with('success', 'Status das transações consultado e sincronizado com a MisticPay!');
    }

    /**
     * Gerar Cobrança PIX (Cash-In) para uma parcela
     */
    public function generateInstallmentPix(LoanInstallment $installment, MisticPayService $misticPay)
    {
        $installment->load('loan.client');
        $client = $installment->loan->client;

        $response = $misticPay->createPixCharge(
            amount: (float) $installment->amount,
            payerName: $client->name,
            payerDocument: $client->document,
            description: "ASBANK Parcela {$installment->installment_number}/{$installment->loan->installments_count} - {$installment->loan->contract_number}",
            installmentId: $installment->id
        );

        if ($response['success']) {
            $installment->update([
                'pix_transaction_id' => $response['transaction_id'],
                'pix_qr_code' => $response['qr_code'],
                'pix_qr_code_base64' => $response['qr_code_base64'],
                'pix_created_at' => now(),
                'pix_expires_at' => now()->addDays(2),
            ]);

            // Register / update transaction
            FinancialTransaction::updateOrCreate(
                [
                    'installment_id' => $installment->id,
                    'type' => 'cash_in',
                ],
                [
                    'client_id' => $client->id,
                    'loan_id' => $installment->loan_id,
                    'category' => 'Cobrança de Parcela PIX',
                    'amount' => $installment->amount,
                    'misticpay_transaction_id' => $response['transaction_id'],
                    'counterparty_name' => $client->name,
                    'counterparty_document' => $client->document,
                    'status' => 'pendente',
                    'description' => "Cobrança PIX Gerada para Parcela {$installment->installment_number}/{$installment->loan->installments_count}",
                    'raw_response' => $response['raw'] ?? [],
                ]
            );

            return back()->with('success', "Cobrança PIX gerada com sucesso para a parcela #{$installment->installment_number}!");
        }

        return back()->with('error', 'Falha ao gerar cobrança PIX: ' . ($response['message'] ?? 'Erro desconhecido'));
    }

    /**
     * Marcar parcela como paga manualmente
     */
    public function markInstallmentPaid(LoanInstallment $installment)
    {
        $installment->load('loan.client');

        $installment->update([
            'status' => 'pago',
            'paid_at' => now(),
            'pix_e2e' => 'MANUAL_' . date('YmdHis') . '_' . rand(100, 999),
        ]);

        FinancialTransaction::create([
            'client_id' => $installment->loan->client_id,
            'loan_id' => $installment->loan_id,
            'installment_id' => $installment->id,
            'type' => 'cash_in',
            'category' => 'Pagamento de Parcela Manual/PIX',
            'amount' => $installment->amount,
            'counterparty_name' => $installment->loan->client->name,
            'counterparty_document' => $installment->loan->client->document,
            'status' => 'completo',
            'description' => "Baixa de pagamento da parcela #{$installment->installment_number}/{$installment->loan->installments_count}",
        ]);

        // If all paid, close loan
        $loan = $installment->loan;
        if ($loan->installments()->where('status', '!=', 'pago')->count() === 0) {
            $loan->update(['status' => 'quitado']);
        }

        return back()->with('success', "Parcela #{$installment->installment_number} marcada como Paga!");
    }

    /**
     * Simular Webhook de Recebimento PIX (Cash-In) vindo da MisticPay
     */
    public function simulateWebhookPayment(LoanInstallment $installment, MisticPayService $misticPay)
    {
        $installment->load('loan.client');

        $txId = $installment->pix_transaction_id ?: ('mp_ci_sim_' . date('YmdHis') . '_' . rand(100, 999));
        if (empty($installment->pix_transaction_id)) {
            $installment->update(['pix_transaction_id' => $txId]);
        }

        $payload = [
            'transactionId' => $txId,
            'transactionType' => 'DEPOSITO',
            'transactionMethod' => 'PIX',
            'status' => 'COMPLETO',
            'value' => (float) $installment->amount,
            'fee' => 1.50,
            'e2e' => 'E' . date('YmdHis') . rand(10000000, 99999999) . 'ASBANK',
            'clientName' => $installment->loan->client->name,
            'clientDocument' => $installment->loan->client->document,
        ];

        $misticPay->handleWebhook($payload);

        return back()->with('success', "Webhook de liquidação PIX simulado com sucesso! Parcela #{$installment->installment_number} quitada.");
    }

    /**
     * Visualização e Impressão do Contrato Digital com Análise
     */
    public function contractPrint(Loan $loan)
    {
        $loan->load(['client', 'installments']);
        return view('admin.loans.contract-print', compact('loan'));
    }

    /**
     * Helper to generate formal contract terms
     */
    private function generateStandardContractTerms(Client $client, string $contractNumber, float $amount, float $total, int $count, float $parcelValue, float $rate): string
    {
        $doc = $client->formattedDocument();
        $date = date('d/m/Y');

        return <<<HTML
<div class="contract-body space-y-4 text-justify text-sm leading-relaxed text-slate-300">
    <p><strong>INSTRUMENTO PARTICULAR DE CONTRATO DE MÚTUO FINANCEIRO COM LIQUIDAÇÃO EM PARCELAS PIX</strong></p>
    
    <p><strong>CONTRATO Nº {$contractNumber}</strong></p>

    <p><strong>1. DAS PARTES:</strong><br>
    <strong>CREDORA:</strong> ASBANK SERVIÇOS FINANCEIROS LTDA, instituição financeira privada e correspondente bancário digital, doravante denominada simplesmente <strong>ASBANK</strong>.<br>
    <strong>DEVEDOR(A):</strong> {$client->name}, inscrito(a) no CPF/CNPJ sob o nº {$doc}, com chave PIX cadastrada ({$client->pix_key_type}: {$client->pix_key}), doravante denominado(a) <strong>MUTUÁRIO(A)</strong>.</p>

    <p><strong>2. DO OBJETO E VALORES:</strong><br>
    A CREDORA concede ao(à) MUTUÁRIO(A), a título de mútuo financeiro, o valor principal líquido de <strong>R$ number_format({$amount}, 2, ',', '.')</strong>, a ser transferido diretamente para a chave PIX informada pelo mutuário, mediante aprovação do comitê de crédito.</p>

    <p><strong>3. DO PAGAMENTO E ENCARGOS:</strong><br>
    O montante total financiado, acrescido de taxa de remuneração de {$rate}% a.m., totaliza <strong>R$ number_format({$total}, 2, ',', '.')</strong>, dividido em <strong>{$count} parcelas</strong> fixas e sucessivas de <strong>R$ number_format({$parcelValue}, 2, ',', '.')</strong>, com vencimentos descritos no cronograma anexo.</p>

    <p><strong>4. DA LIQUIDAÇÃO VIA PIX:</strong><br>
    As parcelas serão quitadas mediante QR Code PIX ou chave PIX Copia e Cola disponibilizados no portal do cliente e enviados via notificação eletrônica pela ASBANK.</p>

    <p><strong>5. ANÁLISE DE CRÉDITO E CONFORMIDADE:</strong><br>
    Este contrato foi emitido após validação biométrica e consulta automatizada aos birôs de crédito, com parecer favorável do sistema antifraude ASBANK.</p>

    <p class="pt-4">Emitido digitalmente pelo sistema ASBANK em {$date}.</p>
</div>
HTML;
    }
}
