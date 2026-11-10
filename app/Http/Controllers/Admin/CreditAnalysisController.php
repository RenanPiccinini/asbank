<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'em_analise');
        $search = $request->input('search');

        $counts = [
            'em_analise' => Loan::where('status', 'em_analise')->count(),
            'aprovado' => Loan::where('origin', 'site')->whereIn('status', ['aprovado', 'ativo', 'quitado'])->count(),
            'rejeitado' => Loan::where('status', 'rejeitado')->count(),
            'todos' => Loan::where('origin', 'site')->orWhere('status', 'em_analise')->count(),
        ];

        $query = Loan::with(['client.user'])
            ->orderByRaw("CASE WHEN status = 'em_analise' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc');

        if ($status === 'em_analise') {
            $query->where('status', 'em_analise');
        } elseif ($status === 'aprovado') {
            $query->where('origin', 'site')->whereIn('status', ['aprovado', 'ativo', 'quitado']);
        } elseif ($status === 'rejeitado') {
            $query->where('status', 'rejeitado');
        } else {
            $query->where(function ($q) {
                $q->where('origin', 'site')->orWhere('status', 'em_analise');
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('document', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $proposals = $query->paginate(12)->withQueryString();

        return view('admin.analyses.index', compact('proposals', 'counts', 'status', 'search'));
    }

    public function show(Loan $loan)
    {
        $loan->load(['client.user', 'reviewer', 'installments']);
        $client = $loan->client;

        $monthlyIncome = (float) ($client->monthly_income ?? 0);
        $incomeRatio = $monthlyIncome > 0
            ? round(((float) $loan->installment_value / $monthlyIncome) * 100, 1)
            : null;

        return view('admin.analyses.show', compact('loan', 'client', 'incomeRatio'));
    }

    /**
     * Atualiza/edita os termos da proposta (contraproposta) antes ou depois para envio no WhatsApp
     */
    public function updateProposal(Request $request, Loan $loan)
    {
        if ($loan->disbursement_status === 'enviado' || $loan->paidInstallmentsCount() > 0) {
            return back()->with('error', 'Não é possível alterar valores de uma proposta com PIX já desembolsado ou parcelas pagas.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'installments_count' => ['required', 'integer', 'min:1', 'max:72'],
            'first_due_date' => ['required', 'date'],
            'credit_score' => ['nullable', 'integer', 'min:300', 'max:1000'],
            'credit_analysis_notes' => ['nullable', 'string'],
            'pix_key' => ['nullable', 'string', 'max:150'],
            'pix_key_type' => ['nullable', 'in:cpf,cnpj,email,phone,random'],
        ]);

        $amount = round((float) $validated['amount'], 2);
        $interestRate = round((float) $validated['interest_rate'], 2);
        $installmentsCount = (int) $validated['installments_count'];

        $totalInterest = $amount * (($interestRate / 100) * $installmentsCount);
        $totalAmount = round($amount + $totalInterest, 2);
        $installmentValue = round($totalAmount / $installmentsCount, 2);

        DB::transaction(function () use ($loan, $validated, $amount, $interestRate, $installmentsCount, $totalAmount, $installmentValue) {
            $pixKey = $validated['pix_key'] ?: $loan->pix_key;
            $pixKeyType = $validated['pix_key_type'] ?: $loan->pix_key_type;

            $loan->update([
                'amount' => $amount,
                'interest_rate' => $interestRate,
                'installments_count' => $installmentsCount,
                'total_amount' => $totalAmount,
                'installment_value' => $installmentValue,
                'first_due_date' => $validated['first_due_date'],
                'credit_score' => $validated['credit_score'] ?? $loan->credit_score,
                'credit_analysis_notes' => $validated['credit_analysis_notes'] ?? $loan->credit_analysis_notes,
                'pix_key' => $pixKey,
                'pix_key_type' => $pixKeyType,
            ]);

            if ($loan->installments()->exists()) {
                $loan->installments()->delete();
                $loan->refresh();
                $loan->generateInstallments();
                $loan->update([
                    'contract_terms' => Loan::buildStandardContractTerms(
                        $loan->client,
                        $loan->contract_number,
                        $amount,
                        $totalAmount,
                        $installmentsCount,
                        $installmentValue,
                        $interestRate
                    ),
                ]);
            }
        });

        $loan->refresh();

        if ($request->input('action') === 'save_and_whatsapp') {
            return back()
                ->with('success', 'Proposta atualizada com sucesso! O WhatsApp do cliente foi preparado com os novos valores.')
                ->with('open_whatsapp_url', $loan->whatsappProposalUrl());
        }

        return back()->with('success', 'Condições da proposta atualizadas com sucesso! Você já pode enviar a proposta atualizada pelo WhatsApp.');
    }

    /**
     * Aprova a proposta, gera parcelas/contrato e LIBERA O ACESSO do cliente ao portal
     */
    public function approve(Request $request, Loan $loan)
    {
        if (in_array($loan->status, ['aprovado', 'ativo', 'quitado'])) {
            return back()->with('info', 'Esta proposta já foi aprovada anteriormente.');
        }

        DB::transaction(function () use ($loan) {
            $loan->load('client.user');
            $client = $loan->client;

            $contractTerms = Loan::buildStandardContractTerms(
                $client,
                $loan->contract_number,
                (float) $loan->amount,
                (float) $loan->total_amount,
                (int) $loan->installments_count,
                (float) $loan->installment_value,
                (float) $loan->interest_rate
            );

            $loan->update([
                'status' => 'aprovado',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => null,
                'contract_terms' => $contractTerms,
            ]);

            $loan->generateInstallments();

            $client->update([
                'status' => 'ativo',
                'credit_limit' => max((float) $client->credit_limit, (float) $loan->amount),
            ]);

            if ($client->user) {
                $client->user->update(['is_active' => true]);
            }
        });

        return back()->with('success', "Proposta {$loan->contract_number} APROVADA! Acesso ao Portal do Cliente liberado com sucesso. Agora você pode comunicar o cliente no WhatsApp.");
    }

    /**
     * Reprova a proposta e mantém o acesso do cliente bloqueado
     */
    public function reject(Request $request, Loan $loan)
    {
        if ($loan->disbursement_status === 'enviado' || $loan->paidInstallmentsCount() > 0) {
            return back()->with('error', 'Não é possível reprovar um contrato com PIX já enviado ou parcelas pagas.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($loan, $validated) {
            $loan->load('client.user');

            $loan->update([
                'status' => 'rejeitado',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'rejection_reason' => $validated['rejection_reason'] ?: 'Não aprovado na política atual de análise de crédito.',
            ]);

            $client = $loan->client;
            $hasOtherActiveLoans = $client->loans()
                ->where('id', '!=', $loan->id)
                ->whereIn('status', ['aprovado', 'ativo', 'quitado'])
                ->exists();

            if (!$hasOtherActiveLoans) {
                $client->update(['status' => 'rejeitado']);
                if ($client->user) {
                    $client->user->update(['is_active' => false]);
                }
            }
        });

        return back()->with('info', "Proposta {$loan->contract_number} reprovada.");
    }

    /**
     * Reabre uma proposta reprovada para nova análise
     */
    public function reopen(Loan $loan)
    {
        if ($loan->status !== 'rejeitado') {
            return back()->with('error', 'Apenas propostas reprovadas podem ser reabertas para análise.');
        }

        DB::transaction(function () use ($loan) {
            $loan->load('client.user');
            $loan->update([
                'status' => 'em_analise',
                'rejection_reason' => null,
            ]);

            $client = $loan->client;
            $hasOtherActiveLoans = $client->loans()
                ->where('id', '!=', $loan->id)
                ->whereIn('status', ['aprovado', 'ativo', 'quitado'])
                ->exists();

            if (!$hasOtherActiveLoans) {
                $client->update(['status' => 'pendente']);
                if ($client->user) {
                    $client->user->update(['is_active' => false]);
                }
            }
        });

        return back()->with('success', "Proposta {$loan->contract_number} reaberta para análise!");
    }
}
