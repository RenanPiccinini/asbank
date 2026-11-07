@extends('layouts.client')

@section('content')
<div class="space-y-6">

    <!-- Header & Summary Cards -->
    <div>
        <h1 class="text-xl font-bold text-white flex items-center gap-2">
            <i data-lucide="arrow-left-right" class="w-6 h-6 text-sky-400"></i> Meu Extrato PIX
        </h1>
        <p class="text-xs text-slate-400">Histórico de transferências recebidas (empréstimos) e parcelas pagas via PIX.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-sm">
            <span class="text-xs font-semibold text-slate-400">Total Recebido na Minha Conta</span>
            <div class="text-2xl font-black text-emerald-400 mt-1">
                + R$ {{ number_format($totalRecebido, 2, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Desembolsos de empréstimos transferidos via PIX</p>
        </div>

        <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-sm">
            <span class="text-xs font-semibold text-slate-400">Total Pago em Parcelas</span>
            <div class="text-2xl font-black text-sky-400 mt-1">
                R$ {{ number_format($totalPago, 2, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-1">Parcelas liquidadas via PIX Copia e Cola / QR Code</p>
        </div>
    </div>

    <!-- Statement Table -->
    <div class="rounded-2xl bg-[#0A1324] border border-[#162540] overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-[#070D18] text-xs font-semibold uppercase text-slate-400 tracking-wider border-b border-[#162540]">
                    <tr>
                        <th class="px-6 py-4">Movimentação</th>
                        <th class="px-6 py-4">Data / Horário</th>
                        <th class="px-6 py-4">Identificador E2E</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#162540]">
                    @forelse($transactions as $tx)
                    @php $typeBadge = $tx->typeBadge(); @endphp
                    <tr class="hover:bg-[#0D182E] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl {{ $tx->type === 'cash_out' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-sky-500/10 text-sky-400' }} flex items-center justify-center font-bold">
                                    <i data-lucide="{{ $tx->type === 'cash_out' ? 'arrow-down-left' : 'arrow-up-right' }}" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-white text-xs">
                                        {{ $tx->type === 'cash_out' ? 'Crédito Recebido via PIX' : 'Pagamento de Parcela' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">{{ $tx->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">
                            {{ $tx->created_at->format('d/m/Y') }}
                            <div class="text-[11px] text-slate-400">{{ $tx->created_at->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-400">
                            {{ $tx->e2e ?: ($tx->misticpay_transaction_id ?: '---') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Concluído
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-base font-black {{ $tx->type === 'cash_out' ? 'text-emerald-400' : 'text-white' }}">
                                {{ $tx->type === 'cash_out' ? '+' : '-' }} R$ {{ number_format($tx->amount, 2, ',', '.') }}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                            Nenhuma movimentação registrada no seu extrato até o momento.
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
