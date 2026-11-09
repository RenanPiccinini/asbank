<?php

namespace App\Console\Commands;

use App\Services\MisticPayService;
use Illuminate\Console\Command;

class TestMisticPayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'misticpay:test 
                            {--type=all : Tipo de teste a executar: all, balance, cashin, cashout, webhook}
                            {--amount=10.00 : Valor para testar a transação}
                            {--document= : CPF ou CNPJ do pagador (se omitido, gera um CPF com dígitos válidos)}
                            {--name=Cliente Teste ASBANK : Nome do pagador}
                            {--pixkey= : Chave PIX para teste de envio Cash-Out}
                            {--pixkeytype=cpf : Tipo da chave PIX (cpf, cnpj, email, phone, random)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a conexão e as funções da API do Gateway MisticPay (Cash-In, Cash-Out e Webhook)';

    /**
     * Execute the console command.
     */
    public function handle(MisticPayService $misticPay): int
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>=========================================================</>');
        $this->line('<fg=cyan;options=bold>   ASBANK • Diagnóstico & Teste da API MisticPay PIX     </>');
        $this->line('<fg=cyan;options=bold>=========================================================</>');
        $this->newLine();

        $baseUrl = config('services.misticpay.base_url', 'https://api.misticpay.com');
        $pubKey = config('services.misticpay.public_key');
        $secKey = config('services.misticpay.secret_key');
        $isMock = $misticPay->isMockMode();

        $maskedPub = !empty($pubKey) ? substr($pubKey, 0, 8) . '...' . substr($pubKey, -4) : '<fg=red>Não configurada</>';
        $maskedSec = !empty($secKey) ? '••••••••••••••••' . substr($secKey, -4) : '<fg=red>Não configurada</>';

        $this->table(
            ['Parâmetro', 'Valor Atual (.env)'],
            [
                ['URL Base', $baseUrl],
                ['MISTICPAY_PUBLIC_KEY', $maskedPub],
                ['MISTICPAY_SECRET_KEY', $maskedSec],
                ['MISTICPAY_MOCK', config('services.misticpay.mock') ? 'true' : 'false'],
                ['Modo Efetivo de Execução', $isMock ? '<fg=yellow;options=bold>SIMULAÇÃO / MOCK</>' : '<fg=green;options=bold>PRODUÇÃO REAL</>'],
                ['Webhook URL do Sistema', $misticPay->getWebhookUrl()],
            ]
        );

        $this->newLine();

        $type = strtolower($this->option('type') ?? 'all');
        $amount = (float) ($this->option('amount') ?? 10.00);

        $allPassed = true;

        // 1. Test Balance & Auth
        if (in_array($type, ['all', 'balance'])) {
            $allPassed = $this->testBalance($misticPay) && $allPassed;
        }

        // 2. Test Cash-In (PIX Charge)
        if (in_array($type, ['all', 'cashin'])) {
            $allPassed = $this->testCashIn($misticPay, $amount) && $allPassed;
        }

        // 3. Test Cash-Out (PIX Withdraw)
        if (in_array($type, ['all', 'cashout'])) {
            $allPassed = $this->testCashOut($misticPay, $amount) && $allPassed;
        }

        // 4. Test Webhook
        if (in_array($type, ['all', 'webhook'])) {
            $allPassed = $this->testWebhook($misticPay, $amount) && $allPassed;
        }

        $this->newLine();
        if ($allPassed) {
            $this->info('✔ Todos os testes foram executados com sucesso!');
        } else {
            $this->warn('⚠ Alguns testes reportaram observações ou falhas.');
        }
        $this->newLine();

        return $allPassed ? 0 : 1;
    }

    private function testBalance(MisticPayService $misticPay): bool
    {
        $this->line('<fg=yellow;options=bold>[1/4] Testando Autenticação e Consulta de Saldo (/api/users/balance)...</>');

        if (!$misticPay->isConfigured()) {
            $this->warn('  [PULADO] Chaves de API não configuradas no .env. Configure MISTICPAY_PUBLIC_KEY e MISTICPAY_SECRET_KEY para testar contra o gateway real.');
            return true;
        }

        $result = $misticPay->testConnection();

        if ($result['success']) {
            $this->info('  ✔ ' . $result['message']);
            return true;
        }

        $this->error('  ✖ ' . $result['message']);
        return false;
    }

    private function testCashIn(MisticPayService $misticPay, float $amount): bool
    {
        $this->line('<fg=yellow;options=bold>[2/4] Testando Geração de Cobrança PIX Cash-In (/api/transactions/create)...</>');

        $doc = preg_replace('/\D/', '', $this->option('document') ?: $this->generateValidCpf());
        $name = $this->option('name') ?: 'Cliente Teste ASBANK';

        $this->line("     - Pagador        : {$name} (Documento: {$doc})");

        $response = $misticPay->createPixCharge(
            amount: $amount,
            payerName: $name,
            payerDocument: $doc,
            description: 'Teste Cobranca PIX ASBANK'
        );

        if ($response['success']) {
            $mode = $response['is_mock'] ? '(Modo Simulação)' : '(Modo Produção MisticPay)';
            $this->info("  ✔ Cobrança PIX criada com sucesso! {$mode}");
            $this->line("     - Transaction ID : " . ($response['transaction_id'] ?? 'N/A'));
            $this->line("     - Valor          : R$ " . number_format($amount, 2, ',', '.'));
            $this->line("     - Copia e Cola   : " . substr($response['qr_code'] ?? '', 0, 45) . '...');
            $this->line("     - QR Code Base64 : " . (!empty($response['qr_code_base64']) ? 'Gerado com sucesso' : 'Vazio'));
            return true;
        }

        $this->error('  ✖ Falha ao criar cobrança PIX: ' . ($response['message'] ?? 'Erro desconhecido'));

        if (str_contains(strtolower($response['message'] ?? ''), 'adquirente')) {
            $this->warn('     Dica: A adquirente bancária da MisticPay exige um CPF com dígitos válidos (ou CPF real).');
            $this->line('     Você pode passar seu próprio documento: <fg=cyan>php artisan misticpay:test --type=cashin --document=SEU_CPF --name="SEU NOME"</>');
        }
        return false;
    }

    private function testCashOut(MisticPayService $misticPay, float $amount): bool
    {
        $this->line('<fg=yellow;options=bold>[3/4] Testando Envio de PIX Cash-Out / Desembolso (/api/transactions/withdraw)...</>');

        $doc = preg_replace('/\D/', '', $this->option('document') ?: $this->generateValidCpf());
        $name = $this->option('name') ?: 'Cliente Teste ASBANK';
        $pixKey = $this->option('pixkey') ?: $doc;
        $pixKeyType = $this->option('pixkeytype') ?: 'cpf';

        $response = $misticPay->sendPixWithdraw(
            amount: $amount,
            pixKey: $pixKey,
            pixKeyType: $pixKeyType,
            beneficiaryName: $name,
            beneficiaryDoc: $doc,
            description: 'Teste Cash-Out ASBANK'
        );

        if ($response['success']) {
            $mode = $response['is_mock'] ? '(Modo Simulação)' : '(Modo Produção MisticPay)';
            $this->info("  ✔ Desembolso PIX processado com sucesso! {$mode}");
            $this->line("     - Transaction ID : " . ($response['transaction_id'] ?? 'N/A'));
            $this->line("     - End-to-End     : " . ($response['e2e'] ?? 'Aguardando processamento'));
            $this->line("     - Status         : " . ($response['status'] ?? 'N/A'));
            return true;
        }

        $this->error('  ✖ Falha no envio de PIX: ' . ($response['message'] ?? 'Erro desconhecido'));

        if (str_contains(strtolower($response['message'] ?? ''), 'saldo insuficiente')) {
            $this->comment('     ℹ Nota: Sua conta MisticPay está sem saldo disponível (R$ 0,00). Para transferir dinheiro real, efetue um depósito/Pix para sua conta MisticPay.');
        }

        return false;
    }

    private function testWebhook(MisticPayService $misticPay, float $amount): bool
    {
        $this->line('<fg=yellow;options=bold>[4/4] Testando Processamento de Webhook (/api/webhooks/misticpay)...</>');

        $simTxId = 'test_webhook_' . time();
        $payload = [
            'transactionId' => $simTxId,
            'transactionType' => 'DEPOSITO',
            'transactionMethod' => 'PIX',
            'status' => 'COMPLETO',
            'value' => $amount,
            'fee' => 1.50,
            'e2e' => 'E999' . time() . 'ASBANKTEST',
            'clientName' => 'Cliente Teste ASBANK',
            'clientDocument' => '12345678901',
        ];

        $result = $misticPay->handleWebhook($payload);

        if ($result['success']) {
            $this->info('  ✔ Webhook interpretado e processado com sucesso!');
            $this->line("     - Transaction ID : {$simTxId}");
            $this->line("     - Processado     : " . ($result['processed'] ? 'Sim (Entidade atualizada)' : 'Sim (Recebido sem match pendente)'));
            return true;
        }

        $this->error('  ✖ Falha no processamento do Webhook.');
        return false;
    }

    /**
     * Gera um CPF matematicamente válido com dígitos verificadores corretos.
     */
    private function generateValidCpf(): string
    {
        $n = [rand(1, 9)];
        for ($i = 1; $i < 8; $i++) {
            $n[] = rand(0, 9);
        }
        $n[] = rand(1, 9);

        // Primeiro dígito verificador
        $s = 0;
        for ($i = 0; $i < 9; $i++) {
            $s += $n[$i] * (10 - $i);
        }
        $r = $s % 11;
        $n[9] = ($r < 2) ? 0 : 11 - $r;

        // Segundo dígito verificador
        $s = 0;
        for ($i = 0; $i < 10; $i++) {
            $s += $n[$i] * (11 - $i);
        }
        $r = $s % 11;
        $n[10] = ($r < 2) ? 0 : 11 - $r;

        return implode('', $n);
    }
}
