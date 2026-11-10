<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PublicLoanApplicationController extends Controller
{
    /**
     * Formulário público de contratação com dados pré-preenchidos do simulador
     */
    public function create(Request $request)
    {
        $amount = (float) $request->query('amount', 5000);
        if ($amount < 0.01) {
            $amount = 5000.00;
        }
        if ($amount > 1000000) {
            $amount = 1000000.00;
        }

        $installmentsCount = (int) $request->query('installments', 12);
        if ($installmentsCount < 1) {
            $installmentsCount = 1;
        }
        if ($installmentsCount > 48) {
            $installmentsCount = 48;
        }

        $interestRate = 2.50;
        $totalInterest = $amount * (($interestRate / 100) * $installmentsCount);
        $totalAmount = round($amount + $totalInterest, 2);
        $installmentValue = round($totalAmount / $installmentsCount, 2);

        return view('loans.apply', compact(
            'amount',
            'installmentsCount',
            'interestRate',
            'totalInterest',
            'totalAmount',
            'installmentValue'
        ));
    }

    /**
     * Recebe os dados completos do cliente, cria conta pendente (bloqueada) e envia proposta para análise
     */
    public function store(Request $request)
    {
        $cleanDocument = preg_replace('/\D/', '', (string) $request->input('document'));
        $cleanPhone = preg_replace('/\D/', '', (string) $request->input('phone'));

        $request->merge([
            'clean_document' => $cleanDocument,
            'clean_phone' => $cleanPhone,
        ]);

        $validated = $request->validate([
            // Simulação / Proposta
            'amount' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'installments_count' => ['required', 'integer', 'min:1', 'max:48'],
            'loan_purpose' => ['required', 'string', 'max:150'],

            // Dados Pessoais & Acesso ao Portal
            'name' => ['required', 'string', 'max:255'],
            'clean_document' => [
                'required',
                'string',
                'min:11',
                'max:14',
                Rule::unique('clients', 'document'),
            ],
            'rg' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['required', 'date', 'before:today'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:clients,email'],
            'clean_phone' => ['required', 'string', 'min:10', 'max:13'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],

            // Renda & Profissão
            'monthly_income' => ['required', 'numeric', 'min:0'],
            'occupation' => ['required', 'string', 'max:120'],

            // Endereço Completo
            'zip_code' => ['required', 'string', 'max:15'],
            'address' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],

            // Dados Bancários & Chave PIX
            'pix_key_type' => ['required', 'in:cpf,cnpj,email,phone,random'],
            'pix_key' => ['required', 'string', 'max:150'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_agency' => ['nullable', 'string', 'max:20'],
            'bank_account' => ['nullable', 'string', 'max:30'],

            // Termos
            'accept_terms' => ['accepted'],
        ], [
            'clean_document.unique' => 'Este CPF/CNPJ já possui uma solicitação ou cadastro no ASBANK.',
            'clean_document.min' => 'Informe um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.',
            'clean_phone.min' => 'Informe um telefone/WhatsApp válido com DDD.',
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'accept_terms.accepted' => 'Você precisa aceitar os termos de análise de crédito para prosseguir.',
        ]);

        $amount = round((float) $validated['amount'], 2);
        $installmentsCount = (int) $validated['installments_count'];
        $interestRate = 2.50;
        $totalInterest = $amount * (($interestRate / 100) * $installmentsCount);
        $totalAmount = round($amount + $totalInterest, 2);
        $installmentValue = round($totalAmount / $installmentsCount, 2);

        DB::beginTransaction();
        try {
            // 1. Cria usuário com acesso BLOQUEADO (is_active = false) até aprovação do Admin
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'client',
                'document' => $cleanDocument,
                'phone' => $cleanPhone,
                'is_active' => false,
            ]);

            // 2. Cria registro de Cliente com status pendente
            $client = Client::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'document' => $cleanDocument,
                'rg' => $validated['rg'] ?? null,
                'phone' => $cleanPhone,
                'birth_date' => $validated['birth_date'],
                'zip_code' => $validated['zip_code'],
                'address' => $validated['address'],
                'number' => $validated['number'],
                'complement' => $validated['complement'] ?? null,
                'neighborhood' => $validated['neighborhood'],
                'city' => $validated['city'],
                'state' => strtoupper($validated['state']),
                'pix_key_type' => $validated['pix_key_type'],
                'pix_key' => trim($validated['pix_key']),
                'bank_name' => $validated['bank_name'],
                'bank_agency' => $validated['bank_agency'] ?? null,
                'bank_account' => $validated['bank_account'] ?? null,
                'monthly_income' => (float) $validated['monthly_income'],
                'occupation' => $validated['occupation'],
                'credit_limit' => $amount,
                'status' => 'pendente',
                'notes' => 'Cadastro realizado pelo site (Solicitação de Empréstimo). Finalidade: ' . $validated['loan_purpose'],
            ]);

            // 3. Cria Proposta de Empréstimo em análise (parcelas são geradas após aprovação)
            $contractNumber = Loan::generateContractNumber();
            $loan = Loan::create([
                'contract_number' => $contractNumber,
                'client_id' => $client->id,
                'amount' => $amount,
                'interest_rate' => $interestRate,
                'installments_count' => $installmentsCount,
                'total_amount' => $totalAmount,
                'installment_value' => $installmentValue,
                'first_due_date' => now()->addMonth()->toDateString(),
                'status' => 'em_analise',
                'origin' => 'site',
                'loan_purpose' => $validated['loan_purpose'],
                'credit_score' => rand(680, 910),
                'credit_analysis_notes' => 'Proposta enviada via Simulador do Site. Aguardando análise da diretoria ASBANK.',
                'pix_key' => trim($validated['pix_key']),
                'pix_key_type' => $validated['pix_key_type'],
                'disbursement_status' => 'nao_enviado',
            ]);

            DB::commit();

            session()->put('last_proposal_contract', $loan->contract_number);

            return redirect()->route('loan.apply.success');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Não foi possível enviar sua solicitação: ' . $e->getMessage());
        }
    }

    /**
     * Página de confirmação após envio da proposta
     */
    public function success()
    {
        $contractNumber = session('last_proposal_contract');
        if (!$contractNumber) {
            return redirect()->route('home');
        }

        $loan = Loan::with('client')->where('contract_number', $contractNumber)->first();
        if (!$loan) {
            return redirect()->route('home');
        }

        return view('loans.apply-success', compact('loan'));
    }
}
