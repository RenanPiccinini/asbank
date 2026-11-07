<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;

class StatementController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type'); // all, cash_out (Envios), cash_in (Recebimentos)
        $status = $request->input('status');
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = FinancialTransaction::with(['client', 'loan', 'installment'])
            ->orderBy('created_at', 'desc');

        if ($type && in_array($type, ['cash_out', 'cash_in'])) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('counterparty_name', 'like', "%{$search}%")
                  ->orWhere('counterparty_document', 'like', "%{$search}%")
                  ->orWhere('misticpay_transaction_id', 'like', "%{$search}%")
                  ->orWhere('e2e', 'like', "%{$search}%")
                  ->orWhere('pix_key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Totals summary across all transactions or filtered
        $totalEnvios = (float) FinancialTransaction::where('type', 'cash_out')
            ->where('status', 'completo')
            ->sum('amount');

        $totalRecebimentos = (float) FinancialTransaction::where('type', 'cash_in')
            ->where('status', 'completo')
            ->sum('amount');

        $saldoLiquido = $totalRecebimentos - $totalEnvios;
        $totalTaxas = (float) FinancialTransaction::where('status', 'completo')->sum('fee');

        $transactions = $query->paginate(15)->withQueryString();

        return view('admin.statement.index', compact(
            'transactions',
            'type',
            'status',
            'search',
            'dateFrom',
            'dateTo',
            'totalEnvios',
            'totalRecebimentos',
            'saldoLiquido',
            'totalTaxas'
        ));
    }
}
