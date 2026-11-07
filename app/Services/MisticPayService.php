<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\GatewaySetting;
use App\Models\Loan;
use App\Models\LoanInstallment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MisticPayService
{
    protected string $baseUrl;
    protected ?string $publicKey;
    protected ?string $secretKey;
    protected bool $isMock;

    public function __construct()
    {
        $this->baseUrl = rtrim(GatewaySetting::get('misticpay_base_url', config('services.misticpay.base_url', 'https://api.misticpay.com')), '/');
        $this->publicKey = GatewaySetting::get('misticpay_public_key', config('services.misticpay.public_key'));
        $this->secretKey = GatewaySetting::get('misticpay_secret_key', config('services.misticpay.secret_key'));
        
        $mockSetting = GatewaySetting::get('misticpay_mock', config('services.misticpay.mock', 'false'));
        $this->isMock = filter_var($mockSetting, FILTER_VALIDATE_BOOLEAN) || empty($this->publicKey) || empty($this->secretKey);
    }

    public function isConfigured(): bool
    {
        return !empty($this->publicKey) && !empty($this->secretKey);
    }

    public function isMockMode(): bool
    {
        return $this->isMock;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function getWebhookUrl(): string
    {
        return url('/api/webhooks/misticpay');
    }

    /**
     * Create PIX billing (Cash-In)
     */
    public function createPixCharge(
        float $amount,
        string $payerName,
        string $payerDocument,
        string $description = 'Cobrança Empréstimo ASBANK',
        ?string $callbackUrl = null,
        ?int $installmentId = null
    ): array {
        $cleanDoc = preg_replace('/\D/', '', $payerDocument);
        $callbackUrl = $callbackUrl ?: $this->getWebhookUrl();
        $formattedAmount = round($amount, 2);

        // If in mock mode or unconfigured, generate simulated QR Code & payload
        if ($this->isMock) {
            $simTransactionId = 'mp_sim_' . date('YmdHis') . '_' . rand(1000, 9999);
            $copiaECola = sprintf(
                '00020126580014br.gov.bcb.pix0136ASBANK-%s52040000530398654%.2f5802BR5920ASBANK SERVICOS FIN6009SAO PAULO62070503***6304%04X',
                $simTransactionId,
                $formattedAmount,
                rand(1000, 9999)
            );

            // Generate clean SVG QR code
            try {
                $svg = QrCode::size(260)->generate($copiaECola);
                $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
            } catch (\Throwable $e) {
                $qrCodeBase64 = '';
            }

            return [
                'success' => true,
                'is_mock' => true,
                'transaction_id' => $simTransactionId,
                'qr_code' => $copiaECola,
                'qr_code_base64' => $qrCodeBase64,
                'status' => 'PENDENTE',
                'amount' => $formattedAmount,
                'message' => 'Cobrança PIX gerada com sucesso (Modo Simulação)',
                'raw' => [
                    'mode' => 'mock',
                    'transactionId' => $simTransactionId,
                    'amount' => $formattedAmount,
                ],
            ];
        }

        // Production API call
        try {
            $auth = base64_encode($this->publicKey . ':' . $this->secretKey);
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/api/transactions/create', [
                'amount' => $formattedAmount,
                'payerName' => $payerName,
                'payerDocument' => $cleanDoc,
                'description' => $description,
                'callbackUrl' => $callbackUrl,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $transactionId = $data['transactionId'] ?? $data['id'] ?? (string) rand(100000, 999999);
                $qrCode = $data['qrCode'] ?? $data['pixCopyPaste'] ?? $data['qrcode'] ?? '';
                
                // If base64 qrCode is not provided, generate from copia e cola
                $qrCodeBase64 = $data['qrCodeBase64'] ?? $data['qrcodeBase64'] ?? '';
                if (empty($qrCodeBase64) && !empty($qrCode)) {
                    $svg = QrCode::size(260)->generate($qrCode);
                    $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
                }

                return [
                    'success' => true,
                    'is_mock' => false,
                    'transaction_id' => (string) $transactionId,
                    'qr_code' => $qrCode,
                    'qr_code_base64' => $qrCodeBase64,
                    'status' => $data['status'] ?? 'PENDENTE',
                    'amount' => $formattedAmount,
                    'message' => 'Cobrança PIX gerada via MisticPay',
                    'raw' => $data,
                ];
            }

            Log::error('MisticPay createPixCharge Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'is_mock' => false,
                'transaction_id' => null,
                'message' => 'Erro na API MisticPay: ' . ($response->json('message') ?? $response->body()),
                'raw' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('MisticPay Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'is_mock' => false,
                'transaction_id' => null,
                'message' => 'Falha na comunicação com o gateway MisticPay: ' . $e->getMessage(),
                'raw' => null,
            ];
        }
    }

    /**
     * Send PIX Cash-Out / Desembolso to client
     */
    public function sendPixWithdraw(
        float $amount,
        string $pixKey,
        string $pixKeyType,
        string $beneficiaryName,
        string $beneficiaryDoc,
        ?string $description = 'Liberação Empréstimo ASBANK',
        ?string $callbackUrl = null
    ): array {
        $cleanDoc = preg_replace('/\D/', '', $beneficiaryDoc);
        $callbackUrl = $callbackUrl ?: $this->getWebhookUrl();
        $formattedAmount = round($amount, 2);

        // Map pix key type to MisticPay expected format
        $typeMapping = [
            'cpf' => 'CPF',
            'cnpj' => 'CNPJ',
            'email' => 'EMAIL',
            'phone' => 'PHONE',
            'random' => 'EVP',
        ];
        $mappedType = $typeMapping[strtolower($pixKeyType)] ?? 'CPF';

        // Mock mode
        if ($this->isMock) {
            $simTransactionId = 'mp_out_' . date('YmdHis') . '_' . rand(1000, 9999);
            $simE2E = 'E' . date('YmdHis') . rand(10000000, 99999999) . 'ASBANK';

            return [
                'success' => true,
                'is_mock' => true,
                'transaction_id' => $simTransactionId,
                'e2e' => $simE2E,
                'status' => 'COMPLETO',
                'amount' => $formattedAmount,
                'message' => 'PIX enviado com sucesso (Modo Simulação)',
                'raw' => [
                    'mode' => 'mock',
                    'transactionId' => $simTransactionId,
                    'e2e' => $simE2E,
                    'status' => 'COMPLETO',
                ],
            ];
        }

        // Live API call
        try {
            $auth = base64_encode($this->publicKey . ':' . $this->secretKey);
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/api/transactions/withdraw', [
                'amount' => $formattedAmount,
                'pixKey' => $pixKey,
                'pixKeyType' => $mappedType,
                'description' => $description,
                'callbackUrl' => $callbackUrl,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $transactionId = $data['transactionId'] ?? $data['id'] ?? (string) rand(100000, 999999);
                $e2e = $data['e2e'] ?? null;
                $status = $data['status'] ?? 'PROCESSANDO';

                return [
                    'success' => true,
                    'is_mock' => false,
                    'transaction_id' => (string) $transactionId,
                    'e2e' => $e2e,
                    'status' => $status,
                    'amount' => $formattedAmount,
                    'message' => 'Solicitação de envio PIX processada via MisticPay',
                    'raw' => $data,
                ];
            }

            Log::error('MisticPay sendPixWithdraw Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'is_mock' => false,
                'transaction_id' => null,
                'e2e' => null,
                'message' => 'Erro ao enviar PIX pela MisticPay: ' . ($response->json('message') ?? $response->body()),
                'raw' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('MisticPay Withdraw Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'is_mock' => false,
                'transaction_id' => null,
                'e2e' => null,
                'message' => 'Falha de conexão com MisticPay: ' . $e->getMessage(),
                'raw' => null,
            ];
        }
    }

    /**
     * Process Webhook payload sent by MisticPay
     */
    public function handleWebhook(array $payload): array
    {
        Log::info('MisticPay Webhook Received', $payload);

        $transactionId = (string) ($payload['transactionId'] ?? $payload['id'] ?? '');
        $transactionType = strtoupper($payload['transactionType'] ?? $payload['type'] ?? '');
        $status = strtoupper($payload['status'] ?? '');
        $value = (float) ($payload['value'] ?? $payload['amount'] ?? 0);
        $e2e = $payload['e2e'] ?? null;

        $processed = false;
        $entityType = null;
        $entityId = null;

        // 1. Check if it matches a Loan Installment (Recebimento / Cash-In)
        $installment = LoanInstallment::where('pix_transaction_id', $transactionId)->first();
        if ($installment) {
            $entityType = 'installment';
            $entityId = $installment->id;
            
            if ($status === 'COMPLETO' || $status === 'PAID' || $status === 'SUCESSO') {
                $installment->update([
                    'status' => 'pago',
                    'paid_at' => now(),
                    'pix_e2e' => $e2e ?? $installment->pix_e2e,
                ]);

                // Create or update FinancialTransaction
                FinancialTransaction::updateOrCreate(
                    [
                        'installment_id' => $installment->id,
                        'type' => 'cash_in',
                    ],
                    [
                        'client_id' => $installment->loan->client_id,
                        'loan_id' => $installment->loan_id,
                        'category' => 'Pagamento de Parcela PIX',
                        'amount' => $installment->amount,
                        'fee' => (float) ($payload['fee'] ?? 0),
                        'misticpay_transaction_id' => $transactionId,
                        'e2e' => $e2e,
                        'counterparty_name' => $payload['clientName'] ?? $installment->loan->client->name,
                        'counterparty_document' => $payload['clientDocument'] ?? $installment->loan->client->document,
                        'status' => 'completo',
                        'description' => "Parcela {$installment->installment_number}/{$installment->loan->installments_count} - Contrato {$installment->loan->contract_number}",
                        'raw_response' => $payload,
                    ]
                );

                // Check if all installments of this loan are paid -> set loan to quitado
                $loan = $installment->loan;
                if ($loan->installments()->where('status', '!=', 'pago')->count() === 0) {
                    $loan->update(['status' => 'quitado']);
                }

                $processed = true;
            }
        }

        // 2. Check if it matches a Loan Disbursement (Envio / Cash-Out)
        $loanDisbursement = Loan::where('disbursement_transaction_id', $transactionId)->first();
        if ($loanDisbursement) {
            $entityType = 'loan_disbursement';
            $entityId = $loanDisbursement->id;

            if ($status === 'COMPLETO' || $status === 'SUCESSO') {
                $loanDisbursement->update([
                    'disbursement_status' => 'enviado',
                    'disbursement_e2e' => $e2e ?? $loanDisbursement->disbursement_e2e,
                    'disbursed_at' => $loanDisbursement->disbursed_at ?: now(),
                    'status' => $loanDisbursement->status === 'aprovado' ? 'ativo' : $loanDisbursement->status,
                ]);

                FinancialTransaction::updateOrCreate(
                    [
                        'loan_id' => $loanDisbursement->id,
                        'type' => 'cash_out',
                    ],
                    [
                        'client_id' => $loanDisbursement->client_id,
                        'category' => 'Desembolso de Empréstimo',
                        'amount' => $loanDisbursement->amount,
                        'fee' => (float) ($payload['fee'] ?? 0),
                        'misticpay_transaction_id' => $transactionId,
                        'e2e' => $e2e,
                        'counterparty_name' => $loanDisbursement->client->name,
                        'counterparty_document' => $loanDisbursement->client->document,
                        'pix_key' => $loanDisbursement->pix_key,
                        'pix_key_type' => $loanDisbursement->pix_key_type,
                        'status' => 'completo',
                        'description' => "Desembolso do Empréstimo {$loanDisbursement->contract_number}",
                        'raw_response' => $payload,
                    ]
                );

                $processed = true;
            } elseif ($status === 'FALHA' || $status === 'CANCELADO' || $status === 'ERRO') {
                $loanDisbursement->update([
                    'disbursement_status' => 'falha',
                ]);
                $processed = true;
            }
        }

        // 3. Check existing FinancialTransaction directly
        $transaction = FinancialTransaction::where('misticpay_transaction_id', $transactionId)->first();
        if ($transaction && !$processed) {
            $transaction->update([
                'status' => ($status === 'COMPLETO' || $status === 'PAID') ? 'completo' : (($status === 'FALHA' || $status === 'ERRO') ? 'falha' : 'pendente'),
                'e2e' => $e2e ?? $transaction->e2e,
                'raw_response' => $payload,
            ]);
            $processed = true;
        }

        return [
            'success' => true,
            'processed' => $processed,
            'transaction_id' => $transactionId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => $status,
        ];
    }
}
