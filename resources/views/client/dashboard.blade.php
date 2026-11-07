@extends('layouts.client')

@section('content')
<div class="space-y-8">

    <!-- Client Welcome Banner -->
    <div class="p-8 rounded-3xl bg-gradient-to-r from-[#0F1D38] via-[#0A1324] to-[#0F1D38] border border-sky-500/30 shadow-2xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 uppercase tracking-wider inline-block mb-3">
                    Portal do Mutuário • ASBANK
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Olá, {{ explode(' ', $client->name)[0] }}! 👋
                </h1>
                <p class="text-sm text-slate-300 mt-1 max-w-xl">
                    Acompanhe seus contratos de empréstimo, parecer da análise de crédito, cronograma de parcelas e pague via PIX instantâneo com baixa automática.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <div class="p-4 rounded-2xl bg-[#070D18]/90 border border-sky-500/30 text-right">
                    <span class="text-xs text-slate-400 block">Seu Limite Aprovado</span>
                    <span class="text-xl font-black text-amber-400">R$ {{ number_format($client->credit_limit, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-sm">
            <span class="text-xs font-semibold text-slate-400">Total Contratado</span>
            <div class="text-2xl font-black text-white mt-1">
                R$ {{ number_format($totalEmprestado, 2, ',', '.') }}
            </div>
            <p class="text-xs text-sky-400 mt-2 flex items-center gap-1">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Recursos liberados na sua conta
            </p>
        </div>

        <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-sm">
            <span class="text-xs font-semibold text-slate-400">Total Liquidado (Pago)</span>
            <div class="text-2xl font-black text-emerald-400 mt-1">
                R$ {{ number_format($totalQuitado, 2, ',', '.') }}
            </div>
            <p class="text-xs text-emerald-400/80 mt-2 flex items-center gap-1">
                <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i> Parcelas pagas via PIX
            </p>
        </div>

        <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-sm">
            <span class="text-xs font-semibold text-slate-400">Saldo a Pagar</span>
            <div class="text-2xl font-black text-amber-300 mt-1">
                R$ {{ number_format($totalAReceber, 2, ',', '.') }}
            </div>
            <p class="text-xs text-amber-400/80 mt-2 flex items-center gap-1">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i> Parcelas restantes
            </p>
        </div>
    </div>

    <!-- Next Upcoming Installments with PIX Payment Button -->
    @if($upcomingInstallments->count() > 0)
    <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-xl">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2.5">
                <i data-lucide="qr-code" class="w-5 h-5 text-amber-400"></i>
                <h2 class="text-base font-bold text-white">Próximas Parcelas para Pagamento via PIX</h2>
            </div>
            <span class="text-xs text-slate-400">Baixa automática em segundos</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($upcomingInstallments as $inst)
            <div class="p-5 rounded-xl bg-[#070D18] border {{ $inst->isOverdue() ? 'border-rose-500/40 bg-rose-950/10' : 'border-[#162540]' }} flex flex-col justify-between space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-white">Parcela #{{ $inst->installment_number }} de {{ $inst->loan->installments_count }}</span>
                        <span class="text-[11px] font-mono {{ $inst->isOverdue() ? 'text-rose-400 font-bold' : 'text-slate-400' }}">
                            Vence: {{ $inst->due_date->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="text-xl font-black text-white">
                        R$ {{ number_format($inst->amount, 2, ',', '.') }}
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Contrato: {{ $inst->loan->contract_number }}
                    </p>
                </div>

                <a href="{{ route('client.loans.pay', $inst) }}" 
                   class="w-full py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-amber-900/20 transition-all">
                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                    <span>Pagar com PIX</span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Contracts & Analysis Overview -->
    <div class="rounded-2xl bg-[#0A1324] border border-[#162540] overflow-hidden shadow-xl">
        <div class="p-6 border-b border-[#162540] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="file-check" class="w-5 h-5 text-sky-400"></i>
                <h2 class="text-base font-bold text-white">Meus Contratos & Análise de Crédito</h2>
            </div>
            <a href="{{ route('client.loans.index') }}" class="text-xs font-semibold text-sky-400 hover:underline">
                Ver todos os contratos
            </a>
        </div>

        <div class="divide-y divide-[#162540]">
            @forelse($loans as $loan)
            @php $badge = $loan->statusBadge(); @endphp
            <div class="p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 hover:bg-[#0D182E] transition-colors">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-sky-600/10 border border-sky-500/20 flex items-center justify-center font-bold text-sky-400 text-sm shrink-0">
                        {{ $loan->installments_count }}x
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3 class="font-bold text-white text-base font-mono">{{ $loan->contract_number }}</h3>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
                                {{ $badge['label'] }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">
                            Valor Liberado: <strong>R$ {{ number_format($loan->amount, 2, ',', '.') }}</strong> • Total c/ Juros: <strong>R$ {{ number_format($loan->total_amount, 2, ',', '.') }}</strong>
                        </p>
                        <div class="flex items-center gap-3 text-xs text-emerald-400 mt-2">
                            <span><i data-lucide="check" class="w-3 h-3 inline"></i> {{ $loan->paidInstallmentsCount() }} parcelas pagas</span>
                            <span>•</span>
                            <span class="text-amber-400">{{ $loan->pendingInstallmentsCount() }} parcelas restantes</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <a href="{{ route('client.loans.contract', $loan) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition-colors flex items-center gap-1.5">
                        <i data-lucide="file-text" class="w-4 h-4"></i> Ver Contrato
                    </a>
                    <a href="{{ route('client.loans.show', $loan) }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold transition-colors flex items-center gap-1.5 shadow">
                        <i data-lucide="eye" class="w-4 h-4"></i> Detalhes & Parcelas
                    </a>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-500 text-sm">
                Nenhum contrato ativo no momento.
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
