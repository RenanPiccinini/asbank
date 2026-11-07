@extends('layouts.admin')

@section('header-title', 'Gerenciador de Empréstimos & PIX')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Search Bar -->
    <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.loans.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-3 w-full">
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por contrato (ASB-...), nome do cliente ou CPF..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
            </div>

            <select name="status" class="w-full sm:w-44 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-slate-300 focus:border-sky-500">
                <option value="">Todos os status</option>
                <option value="aprovado" {{ $status === 'aprovado' ? 'selected' : '' }}>Aprovados</option>
                <option value="ativo" {{ $status === 'ativo' ? 'selected' : '' }}>Ativos</option>
                <option value="quitado" {{ $status === 'quitado' ? 'selected' : '' }}>Quitados</option>
                <option value="em_analise" {{ $status === 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                <option value="cancelado" {{ $status === 'cancelado' ? 'selected' : '' }}>Cancelados</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-medium text-sm transition-colors">
                Filtrar
            </button>
            @if($search || $status || $clientId)
                <a href="{{ route('admin.loans.index') }}" class="text-xs text-slate-400 hover:text-white px-2">Limpar</a>
            @endif
        </form>

        <a href="{{ route('admin.loans.create') }}" class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-sm shadow-lg shadow-amber-900/20 flex items-center justify-center gap-2 uppercase tracking-wider whitespace-nowrap">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Novo Empréstimo</span>
        </a>
    </div>

    <!-- Loans Table -->
    <div class="rounded-2xl bg-[#0A1324] border border-[#162540] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-[#070D18] text-xs font-semibold uppercase text-slate-400 tracking-wider border-b border-[#162540]">
                    <tr>
                        <th class="px-6 py-4">Contrato</th>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4">Valor Principal</th>
                        <th class="px-6 py-4">Parcelamento</th>
                        <th class="px-6 py-4">Envio PIX (MisticPay)</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#162540]">
                    @forelse($loans as $loan)
                    @php 
                        $statusBadge = $loan->statusBadge();
                        $disbBadge = $loan->disbursementBadge();
                    @endphp
                    <tr class="hover:bg-[#0D182E] transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.loans.show', $loan) }}" class="font-mono font-bold text-white hover:text-sky-400 transition-colors">
                                {{ $loan->contract_number }}
                            </a>
                            <div class="text-[11px] text-slate-400 font-normal">{{ $loan->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.clients.show', $loan->client) }}" class="font-bold text-white hover:text-sky-400 transition-colors">
                                {{ $loan->client->name }}
                            </a>
                            <div class="text-xs text-slate-400">CPF: {{ $loan->client->formattedDocument() }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-white text-base">
                                R$ {{ number_format($loan->amount, 2, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                Total c/ juros: R$ {{ number_format($loan->total_amount, 2, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-bold text-white">
                                {{ $loan->installments_count }}x de R$ {{ number_format($loan->installment_value, 2, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-emerald-400 mt-0.5">
                                {{ $loan->paidInstallmentsCount() }} pagas • {{ $loan->pendingInstallmentsCount() }} a vencer
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($loan->disbursement_status === 'enviado')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1.5">
                                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> PIX Enviado
                                </span>
                            @elseif($loan->disbursement_status === 'processando')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/20 inline-flex items-center gap-1.5">
                                    <i data-lucide="loader" class="w-3.5 h-3.5 animate-spin"></i> Processando
                                </span>
                            @else
                                <form method="POST" action="{{ route('admin.loans.send-pix', $loan) }}" onsubmit='return confirmAction(event, "Enviar PIX de Desembolso", "Confirma o envio do PIX de R$ {{ number_format($loan->amount, 2, ',', '.') }} para a chave de {{ $loan->client->name }}? O valor será liberado via MisticPay.", "zap", "amber");'>
                                    @csrf
                                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 transition-colors inline-flex items-center gap-1.5 shadow-md shadow-amber-900/20">
                                        <i data-lucide="send" class="w-3.5 h-3.5"></i> Enviar PIX
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusBadge['class'] }}">
                                {{ $statusBadge['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.loans.show', $loan) }}" 
                                   title="Ver Detalhes e Cobranças PIX" 
                                   class="p-2 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.loans.contract-print', $loan) }}" target="_blank" 
                                   title="Imprimir Contrato Digital" 
                                   class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                                @if($loan->disbursement_status !== 'enviado' && $loan->paidInstallmentsCount() === 0)
                                    <form method="POST" action="{{ route('admin.loans.destroy', $loan) }}" 
                                          onsubmit='return confirmAction(event, "Excluir Contrato", "Tem certeza que deseja excluir permanentemente o contrato {{ $loan->contract_number }}?", "trash-2", "rose");' class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Excluir Contrato" class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                            Nenhum empréstimo encontrado. <a href="{{ route('admin.loans.create') }}" class="text-amber-400 hover:underline">Cadastre um novo empréstimo agora</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($loans->hasPages())
        <div class="p-4 border-t border-[#162540]">
            {{ $loans->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
