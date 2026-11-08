<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GatewaySetting;
use App\Services\MisticPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewaySettingController extends Controller
{
    public function index(MisticPayService $misticPay)
    {
        $baseUrl = GatewaySetting::get('misticpay_base_url', config('services.misticpay.base_url', 'https://api.misticpay.com'));
        $publicKey = GatewaySetting::get('misticpay_public_key', config('services.misticpay.public_key'));
        $secretKey = GatewaySetting::get('misticpay_secret_key', config('services.misticpay.secret_key'));
        $mock = GatewaySetting::get('misticpay_mock', config('services.misticpay.mock', 'false'));
        $isMock = filter_var($mock, FILTER_VALIDATE_BOOLEAN);

        $webhookUrl = $misticPay->getWebhookUrl();
        $isConfigured = $misticPay->isConfigured();

        return view('admin.settings.misticpay', compact(
            'baseUrl',
            'publicKey',
            'secretKey',
            'isMock',
            'webhookUrl',
            'isConfigured'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'misticpay_base_url' => ['required', 'url'],
            'misticpay_public_key' => ['nullable', 'string'],
            'misticpay_secret_key' => ['nullable', 'string'],
            'misticpay_mock' => ['nullable', 'boolean'],
        ]);

        GatewaySetting::set('misticpay_base_url', rtrim($validated['misticpay_base_url'], '/'));
        GatewaySetting::set('misticpay_public_key', $validated['misticpay_public_key'] ?? '');
        GatewaySetting::set('misticpay_secret_key', $validated['misticpay_secret_key'] ?? '');
        GatewaySetting::set('misticpay_mock', $request->boolean('misticpay_mock') ? 'true' : 'false');

        return redirect()->route('admin.settings.misticpay')
            ->with('success', 'Configurações do Gateway MisticPay salvas com sucesso!');
    }

    public function testConnection(MisticPayService $misticPay)
    {
        $baseUrl = rtrim(GatewaySetting::get('misticpay_base_url', config('services.misticpay.base_url', 'https://api.misticpay.com')), '/');
        $publicKey = GatewaySetting::get('misticpay_public_key', config('services.misticpay.public_key'));
        $secretKey = GatewaySetting::get('misticpay_secret_key', config('services.misticpay.secret_key'));

        if (empty($publicKey) || empty($secretKey)) {
            return back()->with('error', 'Chave Pública e Chave Secreta precisam estar preenchidas para testar a conexão com a API da MisticPay.');
        }

        try {
            $auth = base64_encode($publicKey . ':' . $secretKey);
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'ci' => $publicKey,
                'cs' => $secretKey,
                'Accept' => 'application/json',
            ])->get($baseUrl . '/api/users/balance');

            if ($response->successful()) {
                $data = $response->json();
                $balanceValue = $data['balance'] ?? $data['balanceAvailable'] ?? $data['data']['balance'] ?? null;
                $saldo = is_numeric($balanceValue) ? 'Saldo em conta: R$ ' . number_format((float) $balanceValue, 2, ',', '.') : 'Autenticação e chaves confirmadas com sucesso!';
                return back()->with('success', "Conexão com MisticPay estabelecida com sucesso! {$saldo}");
            }

            if ($response->status() === 401 || $response->status() === 403) {
                $msg = $response->json('message') ?? 'Credenciais inválidas ou revogadas.';
                return back()->with('error', "Falha de autenticação na MisticPay (HTTP {$response->status()}): {$msg}. Verifique se a Chave Secreta digitada corresponde exatamente à Chave Pública.");
            }

            $errMsg = $response->json('message') ?? $response->json('error') ?? $response->body();
            return back()->with('error', "Resposta da API MisticPay (HTTP {$response->status()}): {$errMsg}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao conectar à MisticPay: ' . $e->getMessage());
        }
    }
}
