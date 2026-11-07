@extends('layouts.admin')

@section('header-title', 'Detalhes do Cliente: ' . $client->name)

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar para lista de clientes
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.clients.edit', $client) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors flex items-center gap-1.5">
                <i data-lucide="pencil" class="w-4 h-4"></i> Editar Dados
            </a>
            <a href="{{ route('admin.loans.create', ['client_id' => $client->id]) }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-lg shadow-amber-900/20">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Cadastrar Empréstimo
            </a>
        </div>
    </div>

    <!-- Client Header Profile Card -->
    <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-600 to-amber-500 flex items-center justify-center font-black text-white text-xl shadow-lg border border-sky-400/30">
                {{ substr($client->name, 0, 2) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-white">{{ $client->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $client->status === 'ativo' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-slate-500/10 text-slate-400' }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 mt-1">
                    <span class="font-mono text-slate-300">CPF: {{ $client->formattedDocument() }}</span>
                    <span>•</span>
                    <span>{{ $client->email }}</span>
                    <span>•</span>
                    <span>{{ $client->formattedPhone() }}</span>
                </div>
            </div>
        </div>

        <!-- PIX Key Highlight Box -->
        <div class="w-full md:w-auto p-4 rounded-xl bg-[#070D18] border border-amber-500/20 flex items-center gap-4">
            <div class="p-3 rounded-xl bg-amber-500/10 text-amber-400">
                <i data-lucide="zap" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[11px] uppercase tracking-wider text-amber-400 font-bold">Chave PIX Cadastrada (MisticPay)</p>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-sm font-bold text-white">{{ $client->pix_key }}</span>
                    <button onclick="copyToClipboard('{{ $client->pix_key }}', 'Chave PIX copiada!')" class="text-slate-400 hover:text-white" title="Copiar Chave">
                        <i data-lucide="copy" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-[10px] text-slate-400">Tipo: {{ strtoupper($client->pix_key_type) }} • {{ $client->bank_name ?: 'Instituição não informada' }}</p>
            </div>
        </div>
    </div>

    <!-- Client Stats Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540]">
            <span class="text-xs font-semibold text-slate-400">Total Contratado</span>
            <div class="text-xl font-black text-white mt-1">
                R$ {{ number_format($totalEmprestado, 2, ',', '.') }}
            </div>
            <p class="text-[11px] text-sky-400 mt-1">Empréstimos aprovados/ativos</p>
        </div>

        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540]">
            <span class="text-xs font-semibold text-slate-400">Total Quitado</span>
            <div class="text-xl font-black text-emerald-400 mt-1">
                R$ {{ number_format($totalQuitado, 2, ',', '.') }}
            </div>
            <p class="text-[11px] text-emerald-400/80 mt-1">Contratos 100% liquidados</p>
        </div>

        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540]">
            <span class="text-xs font-semibold text-slate-400">Limite de Crédito ASBANK</span>
            <div class="text-xl font-black text-amber-300 mt-1">
                R$ {{ number_format($client->credit_limit, 2, ',', '.') }}
            </div>
            <p class="text-[11px] text-amber-400/80 mt-1">Score de crédito interno</p>
        </div>
    </div>

    <!-- Client Loans Table -->
    <div class="rounded-2xl bg-[#0A1324] border border-[#162540] overflow-hidden">
        <div class="p-6 border-b border-[#162540] flex items-center justify-between">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="banknote" class="w-5 h-5 text-amber-400"></i> Empréstimos & Contratos deste Cliente
            </h2>
            <a href="{{ route('admin.loans.create', ['client_id' => $client->id]) }}" class="text-xs font-bold text-sky-400 hover:text-sky-300 flex items-center gap-1">
                <i data-lucide="plus" class="w-4 h-4"></i> Novo Empréstimo
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-[#070D18] text-xs font-semibold uppercase text-slate-400 tracking-wider border-b border-[#162540]">
                    <tr>
                        <th class="px-6 py-3.5">Contrato</th>
                        <th class="px-6 py-3.5">Valor Principal</th>
                        <th class="px-6 py-3.5">Parcelas</th>
                        <th class="px-6 py-3.5">Status Contrato</th>
                        <th class="px-6 py-3.5">Disparo PIX (MisticPay)</th>
                        <th class="px-6 py-3.5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#162540]">
                    @forelse($client->loans as $loan)
                    @php 
                        $statusBadge = $loan->statusBadge();
                        $disbBadge = $loan->disbursementBadge();
                    @endphp
                    <tr class="hover:bg-[#0D182E] transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-white">
                            <a href="{{ route('admin.loans.show', $loan) }}" class="hover:text-sky-400 transition-colors">
                                {{ $loan->contract_number }}
                            </a>
                            <div class="text-[11px] text-slate-400 font-sans font-normal">{{ $loan->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-6 py-4 font-bold text-white">
                            R$ {{ number_format($loan->amount, 2, ',', '.') }}
                            <div class="text-[11px] text-slate-400 font-normal">Total: R$ {{ number_format($loan->total_amount, 2, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-white">{{ $loan->installments_count }}x</span> de R$ {{ number_format($loan->installment_value, 2, ',', '.') }}
                            <div class="text-[11px] text-emerald-400">{{ $loan->paidInstallmentsCount() }} pagas / {{ $loan->pendingInstallmentsCount() }} abertas</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusBadge['class'] }}">
                                {{ $statusBadge['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($loan->disbursement_status === 'enviado')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> PIX Enviado
                                </span>
                            @else
                                <form method="POST" action="{{ route('admin.loans.send-pix', $loan) }}" onsubmit='return confirm("Confirma o envio do PIX de R$ {{ number_format($loan->amount, 2, ',', '.') }}?");'>
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400 transition-colors inline-flex items-center gap-1 shadow">
                                        <i data-lucide="send" class="w-3.5 h-3.5"></i> Enviar PIX
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.loans.show', $loan) }}" class="p-2 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 transition-colors" title="Gerenciar Parcelas e Cobrança">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.loans.contract-print', $loan) }}" target="_blank" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors" title="Imprimir Contrato">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                            Nenhum empréstimo cadastrado para este cliente.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
