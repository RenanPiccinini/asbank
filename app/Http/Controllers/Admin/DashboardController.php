<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Services\MisticPayService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(MisticPayService $misticPay)
    {
        $totalEmprestado = (float) Loan::whereIn('status', ['ativo', 'aprovado', 'quitado'])->sum('amount');
        $totalRecebido = (float) LoanInstallment::where('status', 'pago')->sum('amount');
        $totalAReceber = (float) LoanInstallment::where('status', '!=', 'pago')->sum('amount');
        $totalClientes = Client::count();
        $totalEmprestimos = Loan::count();
        $pendingAnalysesCount = Loan::where('status', 'em_analise')->count();

        // Extrato stats
        $totalEnviosPix = (float) FinancialTransaction::where('type', 'cash_out')->where('status', 'completo')->sum('amount');
        $totalRecebimentosPix = (float) FinancialTransaction::where('type', 'cash_in')->where('status', 'completo')->sum('amount');

        // Recent loans
        $recentLoans = Loan::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent transactions
        $recentTransactions = FinancialTransaction::with(['client', 'loan'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $gatewayStatus = [
            'is_configured' => $misticPay->isConfigured(),
            'is_mock' => $misticPay->isMockMode(),
            'base_url' => $misticPay->getBaseUrl(),
            'webhook_url' => $misticPay->getWebhookUrl(),
        ];

        return view('admin.dashboard', compact(
            'totalEmprestado',
            'totalRecebido',
            'totalAReceber',
            'totalClientes',
            'totalEmprestimos',
            'pendingAnalysesCount',
            'totalEnviosPix',
            'totalRecebimentosPix',
            'recentLoans',
            'recentTransactions',
            'gatewayStatus'
        ));
    }
}
