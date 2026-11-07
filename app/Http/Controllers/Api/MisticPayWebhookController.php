<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MisticPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MisticPayWebhookController extends Controller
{
    public function handle(Request $request, MisticPayService $misticPay): JsonResponse
    {
        $payload = $request->all();

        Log::info('MisticPay Webhook Endpoint hit', [
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'payload' => $payload,
        ]);

        try {
            $result = $misticPay->handleWebhook($payload);

            return response()->json([
                'success' => true,
                'message' => 'Webhook recebido e processado com sucesso pelo ASBANK',
                'data' => $result,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('MisticPay Webhook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar webhook: ' . $e->getMessage(),
            ], 500);
        }
    }
}
