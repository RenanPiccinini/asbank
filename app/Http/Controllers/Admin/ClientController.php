<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Client::withCount(['loans', 'transactions'])
            ->with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return view('admin.clients.index', compact('clients', 'search', 'status'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $cleanDocument = preg_replace('/\D/', '', $request->input('document'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:clients,email'],
            'document' => ['required', 'string', 'max:20', Rule::unique('clients', 'document')->where(function ($query) use ($cleanDocument) {
                return $query->where('document', $cleanDocument);
            })],
            'phone' => ['nullable', 'string', 'max:25'],
            'birth_date' => ['nullable', 'date'],
            'zip_code' => ['nullable', 'string', 'max:15'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'pix_key' => ['required', 'string', 'max:150'],
            'pix_key_type' => ['required', 'in:cpf,cnpj,email,phone,random'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_agency' => ['nullable', 'string', 'max:20'],
            'bank_account' => ['nullable', 'string', 'max:30'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'create_user_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $validated['document'] = $cleanDocument;

        // Optionally create client login user
        $user = null;
        if ($request->boolean('create_user_account') || !empty($request->input('password'))) {
            $userPassword = $request->input('password') ?: 'asbank123';
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($userPassword),
                'role' => 'client',
                'document' => $cleanDocument,
                'phone' => $validated['phone'] ?? null,
                'is_active' => true,
            ]);
        }

        $validated['user_id'] = $user?->id;
        $validated['status'] = 'ativo';

        $client = Client::create($validated);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Cliente cadastrado com sucesso!' . ($user ? ' Acesso ao portal do cliente criado (Senha padrão: ' . ($request->input('password') ?: 'asbank123') . ')' : ''));
    }

    public function show(Client $client)
    {
        $client->load(['loans.installments', 'transactions', 'user']);
        
        $totalEmprestado = $client->loans()->whereIn('status', ['ativo', 'aprovado', 'quitado'])->sum('amount');
        $totalQuitado = $client->loans()->where('status', 'quitado')->sum('total_amount');
        $totalEmAberto = $client->loans()->where('status', 'ativo')->sum('total_amount');

        return view('admin.clients.show', compact('client', 'totalEmprestado', 'totalQuitado', 'totalEmAberto'));
    }

    public function edit(Client $client)
    {
        $client->load('user');
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $cleanDocument = preg_replace('/\D/', '', $request->input('document'));

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('clients', 'email')->ignore($client->id)],
            'document' => ['required', 'string', 'max:20', Rule::unique('clients', 'document')->ignore($client->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'birth_date' => ['nullable', 'date'],
            'zip_code' => ['nullable', 'string', 'max:15'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'pix_key' => ['required', 'string', 'max:150'],
            'pix_key_type' => ['required', 'in:cpf,cnpj,email,phone,random'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_agency' => ['nullable', 'string', 'max:20'],
            'bank_account' => ['nullable', 'string', 'max:30'],
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:ativo,pendente,rejeitado,inativo,bloqueado'],
            'notes' => ['nullable', 'string'],
            'new_password' => ['nullable', 'string', 'min:6'],
        ]);

        $validated['document'] = $cleanDocument;
        $client->update($validated);

        // Update or create linked User login if requested
        if ($client->user) {
            $client->user->update([
                'name' => $client->name,
                'email' => $client->email,
                'document' => $client->document,
                'phone' => $client->phone,
                'is_active' => $client->status === 'ativo',
            ]);
            if (!empty($request->input('new_password'))) {
                $client->user->update(['password' => Hash::make($request->input('new_password'))]);
            }
        } elseif (!empty($request->input('new_password'))) {
            $user = User::create([
                'name' => $client->name,
                'email' => $client->email,
                'password' => Hash::make($request->input('new_password')),
                'role' => 'client',
                'document' => $client->document,
                'phone' => $client->phone,
                'is_active' => true,
            ]);
            $client->update(['user_id' => $user->id]);
        }

        return redirect()->route('admin.clients.show', $client)->with('success', 'Dados do cliente atualizados com sucesso!');
    }

    public function destroy(Client $client)
    {
        $hasActiveLoans = $client->loans()->whereIn('status', ['ativo', 'aprovado'])->exists();
        if ($hasActiveLoans) {
            return back()->with('error', 'Não é possível excluir este cliente pois ele possui contratos ativos ou aprovados em aberto.');
        }

        $client->delete();
        return redirect()->route('admin.clients.index')->with('success', 'Cliente excluído com sucesso.');
    }
}
