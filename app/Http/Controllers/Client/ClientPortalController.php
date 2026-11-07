<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Services\MisticPayService;
use Illuminate\Http\Request;

class ClientPortalController extends Controller
{
    private function getAuthenticatedClient(): ?Client
    {
        $user = auth()->user();
        if (!$user) return null;

        // Find linked client or client by document / email
        $client = $user->client;
        if (!$client) {
            $client = Client::where('email', $user->email)
                ->orWhere(function ($q) use ($user) {
                    if (!empty($user->document)) {
                        $q->where('document', $user->document);
                    }
                })->first();

            if ($client && !$client->user_id) {
                $client->update(['user_id' => $user->id]);
            }
        }

        return $client;
    }

    public function dashboard()
    {
        $client = $this->getAuthenticatedClient();

        if (!$client) {
            return view('client.no-profile');
        }

        $loans = $client->loans()->with('installments')->get();
        $activeLoans = $loans->whereIn('status', ['ativo', 'aprovado']);

        $totalEmprestado = $loans->whereIn('status', ['ativo', 'aprovado', 'quitado'])->sum('amount');
        $totalQuitado = $loans->sum(fn ($l) => $l->totalPaidAmount());
        $totalAReceber = $loans->sum(fn ($l) => $l->totalRemainingAmount());

        // Upcoming installments to pay
        $upcomingInstallments = LoanInstallment::whereHas('loan', function ($q) use ($client) {
            $q->where('client_id', $client->id);
        })
        ->where('status', '!=', 'pago')
        ->orderBy('due_date', 'asc')
        ->limit(5)
        ->get();

        // Recent personal transactions
        $recentTransactions = FinancialTransaction::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('client.dashboard', compact(
            'client',
            'loans',
            'activeLoans',
            'totalEmprestado',
            'totalQuitado',
            'totalAReceber',
            'upcomingInstallments',
            'recentTransactions'
        ));
    }

    public function loans()
    {
        $client = $this->getAuthenticatedClient();
        if (!$client) return redirect()->route('client.dashboard');

        $loans = $client->loans()->with('installments')->paginate(10);

        return view('client.loans.index', compact('client', 'loans'));
    }

    public function loanDetails(Loan $loan)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client || $loan->client_id !== $client->id) {
            abort(403, 'Acesso não autorizado a este contrato.');
        }

        $loan->load(['installments', 'transactions']);

        return view('client.loans.show', compact('client', 'loan'));
    }

    public function payInstallment(LoanInstallment $installment, MisticPayService $misticPay)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client || $installment->loan->client_id !== $client->id) {
            abort(403, 'Acesso não autorizado a esta parcela.');
        }

        $installment->load('loan');

        // If PIX is not generated yet, generate it now via MisticPay
        if (empty($installment->pix_qr_code)) {
            $response = $misticPay->createPixCharge(
                amount: (float) $installment->amount,
                payerName: $client->name,
                payerDocument: $client->document,
                description: "ASBANK Parcela {$installment->installment_number}/{$installment->loan->installments_count} - Contrato {$installment->loan->contract_number}",
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
            }
        }

        return view('client.loans.pay-installment', compact('client', 'installment'));
    }

    public function checkInstallmentStatus(LoanInstallment $installment)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client || $installment->loan->client_id !== $client->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'status' => $installment->status,
            'is_paid' => $installment->status === 'pago',
            'paid_at' => $installment->paid_at?->format('d/m/Y H:i:s'),
            'e2e' => $installment->pix_e2e,
        ]);
    }

    public function statement()
    {
        $client = $this->getAuthenticatedClient();
        if (!$client) return redirect()->route('client.dashboard');

        $transactions = FinancialTransaction::where('client_id', $client->id)
            ->with(['loan', 'installment'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalRecebido = FinancialTransaction::where('client_id', $client->id)
            ->where('type', 'cash_out')
            ->where('status', 'completo')
            ->sum('amount');

        $totalPago = FinancialTransaction::where('client_id', $client->id)
            ->where('type', 'cash_in')
            ->where('status', 'completo')
            ->sum('amount');

        return view('client.statement', compact('client', 'transactions', 'totalRecebido', 'totalPago'));
    }

    public function contract(Loan $loan)
    {
        $client = $this->getAuthenticatedClient();
        if (!$client || $loan->client_id !== $client->id) {
            abort(403, 'Acesso não autorizado a este contrato.');
        }

        $loan->load(['client', 'installments']);

        return view('client.loans.contract', compact('client', 'loan'));
    }
}
