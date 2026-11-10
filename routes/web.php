<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\StatementController;
use App\Http\Controllers\Client\ClientPortalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard dispatcher based on role
Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('client.dashboard');
})->middleware(['auth'])->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ==========================================
// ADMIN MANAGER ROUTES (Menu na Esquerda)
// ==========================================
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Clientes (adiciona, edita, exclui)
    Route::resource('clients', ClientController::class)->names([
        'index' => 'clients.index',
        'create' => 'clients.create',
        'store' => 'clients.store',
        'show' => 'clients.show',
        'edit' => 'clients.edit',
        'update' => 'clients.update',
        'destroy' => 'clients.destroy',
    ])->parameters(['clients' => 'client']);

    // Empréstimos (cadastra, edita, exclui, envia pix, gera cobrança)
    Route::resource('loans', LoanController::class)->names([
        'index' => 'loans.index',
        'create' => 'loans.create',
        'store' => 'loans.store',
        'show' => 'loans.show',
        'edit' => 'loans.edit',
        'update' => 'loans.update',
        'destroy' => 'loans.destroy',
    ])->parameters(['loans' => 'loan']);

    // Ações especiais de Empréstimos
    Route::post('loans/{loan}/enviar-pix', [LoanController::class, 'sendPixDisbursement'])->name('loans.send-pix');
    Route::get('loans/{loan}/check-status', [LoanController::class, 'checkPixStatus'])->name('loans.check-status');
    Route::post('loans/{loan}/sincronizar-pix', [LoanController::class, 'syncPixStatus'])->name('loans.sync-pix');
    Route::post('installments/{installment}/gerar-pix', [LoanController::class, 'generateInstallmentPix'])->name('loans.generate-installment-pix');
    Route::post('installments/{installment}/marcar-pago', [LoanController::class, 'markInstallmentPaid'])->name('loans.mark-installment-paid');
    Route::post('installments/{installment}/simular-webhook', [LoanController::class, 'simulateWebhookPayment'])->name('loans.simulate-webhook');
    Route::get('loans/{loan}/contrato-impressao', [LoanController::class, 'contractPrint'])->name('loans.contract-print');

    // Extrato (envios e recebimentos)
    Route::get('extrato', [StatementController::class, 'index'])->name('statement.index');

    // Redirecionamento legado caso alguém acesse a URL antiga de configurações
    Route::get('configuracoes/misticpay', fn () => redirect()->route('admin.dashboard'))->name('settings.misticpay');
});

// ==========================================
// CLIENT PORTAL ROUTES (Painel do Cliente)
// ==========================================
Route::middleware(['auth', 'client'])->prefix('cliente')->name('client.')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [ClientPortalController::class, 'dashboard']);

    Route::get('/emprestimos', [ClientPortalController::class, 'loans'])->name('loans.index');
    Route::get('/emprestimos/{loan}', [ClientPortalController::class, 'loanDetails'])->name('loans.show');
    Route::get('/emprestimos/{loan}/contrato', [ClientPortalController::class, 'contract'])->name('loans.contract');
    Route::get('/parcelas/{installment}/pagar', [ClientPortalController::class, 'payInstallment'])->name('loans.pay');
    Route::get('/parcelas/{installment}/status', [ClientPortalController::class, 'checkInstallmentStatus'])->name('loans.installment-status');

    Route::get('/extrato', [ClientPortalController::class, 'statement'])->name('statement');
});

require __DIR__.'/auth.php';
