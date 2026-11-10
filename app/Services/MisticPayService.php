<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Loan;
use App\Models\LoanInstallment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MisticPayService
{
    protected string $baseUrl;
    protected ?string $publicKey;
    protected ?string $secretKey;
    protected bool $isMock;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.misticpay.base_url', 'https://api.misticpay.com'), '/');
        $this->publicKey = config('services.misticpay.public_key') ? (string) config('services.misticpay.public_key') : null;
        $this->secretKey = config('services.misticpay.secret_key') ? (string) config('services.misticpay.secret_key') : null;

        $mockConfig = config('services.misticpay.mock', false);
        $this->isMock = filter_var($mockConfig, FILTER_VALIDATE_BOOLEAN) || empty($this->publicKey) || empty($this->secretKey);
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
     * Build authentication headers matching MisticPay API v1
     */
    protected function getAuthHeaders(array $extra = []): array
    {
        $auth = base64_encode($this->publicKey . ':' . $this->secretKey);

        $headers = [
            'Authorization' => 'Basic ' . $auth,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Legacy ci/cs headers only when not using pk_/sk_ access keys
        if (!str_starts_with((string) $this->publicKey, 'pk_')) {
            $headers['ci'] = $this->publicKey;
            $headers['cs'] = $this->secretKey;
        }

        return array_merge($headers, $extra);
    }

    /**
     * Test connection and credentials with MisticPay API
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Chave Pública (MISTICPAY_PUBLIC_KEY) ou Chave Secreta (MISTICPAY_SECRET_KEY) não estão preenchidas no .env.',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getAuthHeaders())
                ->get($this->baseUrl . '/api/users/balance');

            if ($response->successful()) {
                $data = $response->json() ?? [];
                $balanceValue = $data['balance'] ?? $data['balanceAvailable'] ?? $data['data']['balance'] ?? null;
                $saldo = is_numeric($balanceValue)
                    ? 'Saldo em conta: R$ ' . number_format((float) $balanceValue, 2, ',', '.')
                    : 'Autenticação e chaves confirmadas com sucesso!';

                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message' => "Conexão com MisticPay estabelecida com sucesso! {$saldo}",
                    'balance' => $balanceValue,
                    'raw' => $data,
                ];
            }

            if ($response->status() === 401 || $response->status() === 403) {
                $msg = $response->json('message') ?? 'Credenciais inválidas ou não autorizadas.';
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'message' => "Falha de autenticação na MisticPay (HTTP {$response->status()}): {$msg}. Verifique se MISTICPAY_PUBLIC_KEY e MISTICPAY_SECRET_KEY no .env estão corretas.",
                    'raw' => $response->json(),
                ];
            }

            $errMsg = $response->json('message') ?? $response->json('error') ?? $response->body();
            return [
                'success' => false,
                'status' => $response->status(),
                'message' => "Resposta da API MisticPay (HTTP {$response->status()}): {$errMsg}",
                'raw' => $response->json(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Erro ao conectar à API MisticPay: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create PIX billing (Cash-In)
     */
    public function createPixCharge(
        float $amount,
        string $payerName,
        string $payerDocument,
        string $description = 'Cobranca Emprestimo ASBANK',
        ?string $callbackUrl = null,
        ?int $installmentId = null
    ): array {
        $cleanDoc = preg_replace('/\D/', '', $payerDocument);
        $cleanName = trim(preg_replace('/\s+/', ' ', Str::ascii($payerName)));
        $cleanDesc = mb_substr(trim(Str::ascii($description ?: 'Cobranca Emprestimo ASBANK')), 0, 100);
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

        // Generate unique internal transactionId for MisticPay reconciliation
        $externalTxId = 'ASB' . ($installmentId ? "P{$installmentId}" : '') . date('YmdHis') . rand(100, 999);

        $payload = [
            'amount' => $formattedAmount,
            'payerName' => $cleanName ?: 'Cliente ASBANK',
            'payerDocument' => $cleanDoc,
            'transactionId' => $externalTxId,
            'description' => $cleanDesc,
        ];

        if (!empty($callbackUrl) && !str_contains($callbackUrl, 'localhost') && !str_contains($callbackUrl, '127.0.0.1')) {
            $payload['projectWebhook'] = $callbackUrl;
        }

        // Production API call
        try {
            $response = Http::timeout(15)
                ->withHeaders($this->getAuthHeaders(['X-Request-Id' => $externalTxId]))
                ->post($this->baseUrl . '/api/transactions/create', $payload);

            if ($response->successful()) {
                $json = $response->json() ?? [];
                $data = $json['data'] ?? $json;

                $transactionId = (string) ($data['transactionId'] ?? $data['id'] ?? $externalTxId);
                $qrCode = (string) ($data['copyPaste'] ?? $data['qrCode'] ?? $data['pixCopyPaste'] ?? $data['qrcode'] ?? '');
                $qrCodeBase64 = (string) ($data['qrCodeBase64'] ?? $data['qrcodeBase64'] ?? '');
                $qrcodeUrl = (string) ($data['qrcodeUrl'] ?? '');
                $status = strtoupper((string) ($data['transactionState'] ?? $data['status'] ?? 'PENDENTE'));

                // If base64 qrCode is not provided, generate from copia e cola or fallback to qrcodeUrl
                if (empty($qrCodeBase64) && !empty($qrCode)) {
                    try {
                        $svg = QrCode::size(260)->generate($qrCode);
                        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
                    } catch (\Throwable $e) {
                        $qrCodeBase64 = $qrcodeUrl;
                    }
                } elseif (empty($qrCodeBase64) && !empty($qrcodeUrl)) {
                    $qrCodeBase64 = $qrcodeUrl;
                }

                return [
                    'success' => true,
                    'is_mock' => false,
                    'transaction_id' => $transactionId,
                    'qr_code' => $qrCode,
                    'qr_code_base64' => $qrCodeBase64,
                    'status' => $status,
                    'amount' => $formattedAmount,
                    'message' => 'Cobrança PIX gerada via MisticPay',
                    'raw' => $json,
                ];
            }

            $requestId = $response->header('x-request-id') ?: ($response->json('requestId') ?? null);
            $errorMsg = $response->json('message') ?? $response->json('error') ?? $response->body();

            Log::error('MisticPay createPixCharge Error', [
                'status' => $response->status(),
                'request_id' => $requestId,
                'payload' => $payload,
                'body' => $response->body(),
            ]);

            $detail = $requestId ? " [Ref: {$requestId}]" : '';
            return [
                'success' => false,
                'is_mock' => false,
                'transaction_id' => null,
                'message' => "Erro na API MisticPay (HTTP {$response->status()}): {$errorMsg}{$detail}",
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
        ?string $description = 'Liberacao Emprestimo ASBANK',
        ?string $callbackUrl = null
    ): array {
        $callbackUrl = $callbackUrl ?: $this->getWebhookUrl();
        $formattedAmount = round($amount, 2);

        // Map pix key type to MisticPay exact expected format
        $typeMapping = [
            'cpf' => 'CPF',
            'cnpj' => 'CNPJ',
            'email' => 'EMAIL',
            'phone' => 'TELEFONE',
            'telefone' => 'TELEFONE',
            'random' => 'CHAVE_ALEATORIA',
            'evp' => 'CHAVE_ALEATORIA',
            'chave_aleatoria' => 'CHAVE_ALEATORIA',
        ];
        $mappedType = $typeMapping[strtolower(trim($pixKeyType))] ?? 'CPF';

        // Clean PIX key formatting (MisticPay requires unformatted digits for CPF/CNPJ/TELEFONE)
        $cleanPixKey = trim($pixKey);
        if (in_array($mappedType, ['CPF', 'CNPJ', 'TELEFONE'])) {
            $cleanPixKey = preg_replace('/\D/', '', $cleanPixKey);
            if ($mappedType === 'TELEFONE' && strlen($cleanPixKey) === 13 && str_starts_with($cleanPixKey, '55')) {
                $cleanPixKey = substr($cleanPixKey, 2);
            }
        }

        $cleanDesc = mb_substr(trim(Str::ascii($description ?: 'Liberacao Emprestimo ASBANK')), 0, 100);

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

        $idempotencyKey = 'ASB_OUT_' . md5($cleanPixKey . '_' . $formattedAmount . '_' . date('YmdHi')) . '_' . rand(100, 999);

        $payload = [
            'amount' => $formattedAmount,
            'pixKey' => $cleanPixKey,
            'pixKeyType' => $mappedType,
            'description' => $cleanDesc,
        ];

        if (!empty($callbackUrl) && !str_contains($callbackUrl, 'localhost') && !str_contains($callbackUrl, '127.0.0.1')) {
            $payload['projectWebhook'] = $callbackUrl;
        }

        // Live API call
        try {
            $response = Http::timeout(15)
                ->withHeaders($this->getAuthHeaders(['X-Idempotency-Key' => $idempotencyKey]))
                ->post($this->baseUrl . '/api/transactions/withdraw', $payload);

            if ($response->successful()) {
                $json = $response->json() ?? [];
                $data = $json['data'] ?? $json;

                $transactionId = (string) ($data['transactionId'] ?? $data['id'] ?? $data['jobId'] ?? '');
                $e2e = $data['e2e'] ?? null;
                $status = strtoupper((string) ($data['status'] ?? $data['transactionState'] ?? 'QUEUED'));

                return [
                    'success' => true,
                    'is_mock' => false,
                    'transaction_id' => $transactionId,
                    'e2e' => $e2e,
                    'status' => $status,
                    'amount' => $formattedAmount,
                    'message' => $json['message'] ?? 'Solicitação de envio PIX processada via MisticPay',
                    'raw' => $json,
                ];
            }

            $errorMsg = $response->json('message') ?? $response->json('error') ?? $response->body();

            Log::error('MisticPay sendPixWithdraw Error', [
                'status' => $response->status(),
                'payload' => $payload,
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'is_mock' => false,
                'transaction_id' => null,
                'e2e' => null,
                'message' => "Erro ao enviar PIX pela MisticPay (HTTP {$response->status()}): {$errorMsg}",
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
     * Check transaction status directly on MisticPay API (POST /api/transactions/check)
     */
    public function checkTransaction(string $transactionId): array
    {
        if (empty($transactionId)) {
            return ['success' => false, 'message' => 'ID da transação não informado.'];
        }

        if ($this->isMock) {
            return [
                'success' => true,
                'is_mock' => true,
                'transaction_id' => $transactionId,
                'status' => 'COMPLETO',
                'e2e' => 'E' . date('YmdHis') . rand(10000000, 99999999) . 'ASBANK',
            ];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders($this->getAuthHeaders())
                ->post($this->baseUrl . '/api/transactions/check', [
                    'transactionId' => $transactionId,
                ]);

            if ($response->successful()) {
                $json = $response->json() ?? [];
                $tx = $json['transaction'] ?? $json['data'] ?? $json;

                return [
                    'success' => true,
                    'is_mock' => false,
                    'transaction_id' => (string) ($tx['transactionId'] ?? $transactionId),
                    'status' => strtoupper((string) ($tx['transactionState'] ?? $tx['status'] ?? 'PENDENTE')),
                    'type' => strtoupper((string) ($tx['transactionType'] ?? '')),
                    'value' => (float) ($tx['value'] ?? $tx['amount'] ?? 0),
                    'fee' => (float) ($tx['fee'] ?? 0),
                    'e2e' => $tx['e2e'] ?? null,
                    'raw' => $json,
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao consultar transação na MisticPay: ' . ($response->json('message') ?? $response->body()),
                'raw' => $response->json(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Falha ao consultar transação: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Query MisticPay for a transaction and sync local Loan / Installment state
     */
    public function syncTransactionStatus(string $transactionId): array
    {
        $check = $this->checkTransaction($transactionId);
        if (!$check['success']) {
            return $check;
        }

        $status = $check['status'];
        if (in_array($status, ['COMPLETO', 'COMPLETED', 'PAID', 'SUCESSO', 'CONCLUIDO', 'FALHA', 'FAILED', 'CANCELADO', 'ERRO'])) {
            $this->handleWebhook([
                'transactionId' => $check['transaction_id'],
                'transactionType' => $check['type'] ?? '',
                'status' => $status,
                'value' => $check['value'] ?? 0,
                'fee' => $check['fee'] ?? 0,
                'e2e' => $check['e2e'] ?? null,
            ]);
        }

        return $check;
    }

    /**
     * Process Webhook payload sent by MisticPay
     */
    public function handleWebhook(array $payload): array
    {
        Log::info('MisticPay Webhook Received', $payload);

        $data = $payload['data'] ?? $payload['transaction'] ?? $payload;

        $transactionId = (string) ($data['transactionId'] ?? $payload['transactionId'] ?? $data['id'] ?? $payload['id'] ?? '');
        $status = strtoupper((string) ($data['status'] ?? $data['transactionState'] ?? $payload['status'] ?? $payload['transactionState'] ?? ''));
        $e2e = $data['e2e'] ?? $data['endToEndId'] ?? $payload['e2e'] ?? null;
        $fee = (float) ($data['fee'] ?? $payload['fee'] ?? 0);

        $isSuccessStatus = in_array($status, ['COMPLETO', 'COMPLETED', 'PAID', 'SUCESSO', 'CONCLUIDO', 'APROVADO']);
        $isFailureStatus = in_array($status, ['FALHA', 'FAILED', 'CANCELADO', 'CANCELLED', 'ERRO', 'ERROR', 'REJEITADO', 'REJECTED']);

        $processed = false;
        $entityType = null;
        $entityId = null;

        // 1. Check if it matches a Loan Installment (Recebimento / Cash-In)
        $installment = LoanInstallment::where('pix_transaction_id', $transactionId)->first();
        if ($installment) {
            $entityType = 'installment';
            $entityId = $installment->id;

            if ($isSuccessStatus) {
                $installment->update([
                    'status' => 'pago',
                    'paid_at' => $installment->paid_at ?: now(),
                    'pix_e2e' => $e2e ?? $installment->pix_e2e,
                ]);

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
                        'fee' => $fee,
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

            if ($isSuccessStatus) {
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
                        'fee' => $fee,
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
            } elseif ($isFailureStatus) {
                $loanDisbursement->update([
                    'disbursement_status' => 'falha',
                ]);

                FinancialTransaction::where('loan_id', $loanDisbursement->id)
                    ->where('type', 'cash_out')
                    ->update(['status' => 'falha', 'raw_response' => $payload]);

                $processed = true;
            }
        }

        // 3. Check existing FinancialTransaction directly
        $transaction = FinancialTransaction::where('misticpay_transaction_id', $transactionId)->first();
        if ($transaction && !$processed) {
            $transaction->update([
                'status' => $isSuccessStatus ? 'completo' : ($isFailureStatus ? 'falha' : 'pendente'),
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
