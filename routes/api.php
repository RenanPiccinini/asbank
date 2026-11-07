<?php

use App\Http\Controllers\Api\MisticPayWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// MisticPay Webhook Receiver (Cash-In and Cash-Out notifications)
Route::post('/webhooks/misticpay', [MisticPayWebhookController::class, 'handle'])->name('api.webhooks.misticpay');
