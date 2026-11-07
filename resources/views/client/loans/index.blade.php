@extends('layouts.client')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white flex items-center gap-2">
                <i data-lucide="banknote" class="w-6 h-6 text-amber-400"></i> Meus Empréstimos & Contratos
            </h1>
            <p class="text-xs text-slate-400">Histórico de contratos, status de aprovação e cronograma de pagamentos.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($loans as $loan)
        @php 
            $statusBadge = $loan->statusBadge();
            $paidPercent = $loan->installments_count > 0 ? round(($loan->paidInstallmentsCount() / $loan->installments_count) * 100) : 0;
        @endphp
        <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-5 shadow-xl hover:border-sky-500/30 transition-all flex flex-col justify-between">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">Contrato Digital</span>
                        <div class="text-lg font-black text-white font-mono">{{ $loan->contract_number }}</div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusBadge['class'] }}">
                        {{ $statusBadge['label'] }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 p-4 rounded-xl bg-[#070D18] border border-[#162540]">
                    <div>
                        <span class="text-[11px] text-slate-400">Valor Liberado</span>
                        <div class="text-base font-bold text-white">R$ {{ number_format($loan->amount, 2, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400">Total c/ Juros</span>
                        <div class="text-base font-bold text-sky-400">R$ {{ number_format($loan->total_amount, 2, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400">Parcelas</span>
                        <div class="text-xs font-semibold text-white">{{ $loan->installments_count }}x de R$ {{ number_format($loan->installment_value, 2, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-400">Score de Aprovação</span>
                        <div class="text-xs font-bold text-amber-400">{{ $loan->credit_score }} pontos</div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div>
                    <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                        <span>Progresso de Pagamento</span>
                        <span class="font-bold text-white">{{ $loan->paidInstallmentsCount() }} de {{ $loan->installments_count }} parcelas ({{ $paidPercent }}%)</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-sky-500 to-emerald-400 rounded-full" style="width: {{ $paidPercent }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-3 border-t border-[#162540]">
                <a href="{{ route('client.loans.contract', $loan) }}" class="flex-1 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold text-center transition-colors">
                    Ver Contrato
                </a>
                <a href="{{ route('client.loans.show', $loan) }}" class="flex-1 py-2 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white text-xs font-bold text-center shadow-lg shadow-sky-900/20 transition-all">
                    Ver Parcelas & Pagar
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-2 p-12 text-center text-slate-500 rounded-2xl bg-[#0A1324] border border-[#162540]">
            Você ainda não possui contratos de empréstimo cadastrados.
        </div>
        @endforelse
    </div>

</div>
@endsection
