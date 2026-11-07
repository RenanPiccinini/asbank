@extends('layouts.admin')

@section('header-title', 'Extrato Financeiro PIX')

@section('content')
<div class="space-y-6">

    <!-- Totals Summary Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Envios (Saídas) -->
        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400">Total Enviado (Cash-Out)</span>
                <div class="p-2.5 rounded-xl bg-rose-500/10 text-rose-400">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-rose-400 tracking-tight">
                - R$ {{ number_format($totalEnvios, 2, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Empréstimos transferidos aos clientes</p>
        </div>

        <!-- Total Recebimentos (Entradas) -->
        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400">Total Recebido (Cash-In)</span>
                <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-400">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-400 tracking-tight">
                + R$ {{ number_format($totalRecebimentos, 2, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Cobranças PIX liquidadas</p>
        </div>

        <!-- Saldo Líquido -->
        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400">Saldo Líquido Operacional</span>
                <div class="p-2.5 rounded-xl bg-sky-500/10 text-sky-400">
                    <i data-lucide="scale" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black {{ $saldoLiquido >= 0 ? 'text-emerald-400' : 'text-amber-300' }} tracking-tight">
                R$ {{ number_format($saldoLiquido, 2, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Entradas deduzidas das saídas</p>
        </div>

        <!-- Total de Taxas -->
        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400">Taxas MisticPay</span>
                <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-400">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-400 tracking-tight">
                R$ {{ number_format($totalTaxas, 2, ',', '.') }}
            </div>
            <p class="text-[11px] text-slate-400 mt-2">Tarifas de transação PIX</p>
        </div>
    </div>

    <!-- Filter Bar & Search -->
    <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-4">
        <!-- Quick Type Tabs -->
        <div class="flex flex-wrap items-center gap-2 pb-4 border-b border-[#162540]">
            <a href="{{ route('admin.statement.index', array_merge(request()->except('type', 'page'), ['type' => ''])) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ empty($type) ? 'bg-sky-600 text-white shadow-md' : 'bg-[#070D18] text-slate-300 hover:text-white' }}">
                Todas as Movimentações
            </a>
            <a href="{{ route('admin.statement.index', array_merge(request()->except('type', 'page'), ['type' => 'cash_out'])) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $type === 'cash_out' ? 'bg-rose-600 text-white shadow-md' : 'bg-[#070D18] text-rose-300 hover:text-white' }}">
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i> Apenas Envios (Saídas)
            </a>
            <a href="{{ route('admin.statement.index', array_merge(request()->except('type', 'page'), ['type' => 'cash_in'])) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $type === 'cash_in' ? 'bg-emerald-600 text-white shadow-md' : 'bg-[#070D18] text-emerald-300 hover:text-white' }}">
                <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i> Apenas Recebimentos (Entradas)
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.statement.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @if($type)
                <input type="hidden" name="type" value="{{ $type }}">
            @endif

            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por cliente, CPF, ID MisticPay ou E2E..." 
                       class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-xs text-white focus:border-sky-500">
            </div>

            <div>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-xs text-slate-300 focus:border-sky-500">
                    <option value="">Todos os status</option>
                    <option value="completo" {{ $status === 'completo' ? 'selected' : '' }}>Concluídos</option>
                    <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>Pendentes</option>
                    <option value="falha" {{ $status === 'falha' ? 'selected' : '' }}>Falhas</option>
                </select>
            </div>

            <div>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-xs text-white focus:border-sky-500" title="Data Inicial">
            </div>

            <div class="flex items-center gap-2">
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-xs text-white focus:border-sky-500" title="Data Final">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Statement Table -->
    <div class="rounded-2xl bg-[#0A1324] border border-[#162540] overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-[#070D18] text-xs font-semibold uppercase text-slate-400 tracking-wider border-b border-[#162540]">
                    <tr>
                        <th class="px-6 py-4">Tipo & Operação</th>
                        <th class="px-6 py-4">Cliente / Contraparte</th>
                        <th class="px-6 py-4">Data / Hora</th>
                        <th class="px-6 py-4">Identificador MisticPay / E2E</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Valor Líquido</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#162540]">
                    @forelse($transactions as $tx)
                    @php 
                        $typeBadge = $tx->typeBadge();
                        $statusBadge = $tx->statusBadge();
                    @endphp
                    <tr class="hover:bg-[#0D182E] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $tx->type === 'cash_out' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }} flex items-center justify-center font-bold">
                                    <i data-lucide="{{ $tx->type === 'cash_out' ? 'arrow-up-right' : 'arrow-down-left' }}" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white text-xs">{{ $typeBadge['label'] }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $tx->category }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-white text-xs">
                                {{ $tx->counterparty_name ?: ($tx->client->name ?? 'MisticPay Gateway') }}
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                {{ $tx->counterparty_document ?: ($tx->client ? $tx->client->formattedDocument() : '') }}
                            </div>
                            @if($tx->pix_key)
                                <div class="text-[10px] text-amber-300 font-mono mt-0.5">
                                    Chave: {{ $tx->pix_key }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs font-mono">
                            {{ $tx->created_at->format('d/m/Y') }}
                            <div class="text-[11px] text-slate-400">{{ $tx->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">
                            <div class="text-slate-300">{{ $tx->misticpay_transaction_id ?: '---' }}</div>
                            @if($tx->e2e)
                                <div class="text-[10px] text-slate-500 truncate max-w-[180px]" title="{{ $tx->e2e }}">
                                    E2E: {{ $tx->e2e }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusBadge['class'] }}">
                                {{ $statusBadge['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-base font-black {{ $typeBadge['color'] }}">
                                {{ $typeBadge['sign'] }} R$ {{ number_format($tx->amount, 2, ',', '.') }}
                            </div>
                            @if($tx->fee > 0)
                                <div class="text-[10px] text-slate-400">Taxa: R$ {{ number_format($tx->fee, 2, ',', '.') }}</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            Nenhuma movimentação encontrada com os filtros selecionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
        <div class="p-4 border-t border-[#162540]">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

