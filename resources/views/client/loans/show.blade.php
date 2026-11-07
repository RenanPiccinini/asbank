@extends('layouts.client')

@section('content')
<div class="space-y-6">

    <!-- Top Back Link -->
    <div class="flex items-center justify-between">
        <a href="{{ route('client.loans.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar para meus contratos
        </a>
        <a href="{{ route('client.loans.contract', $loan) }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors flex items-center gap-1.5 shadow">
            <i data-lucide="file-text" class="w-4 h-4"></i> Visualizar Instrumento Contratual
        </a>
    </div>

    <!-- Contract Header Card -->
    @php $statusBadge = $loan->statusBadge(); @endphp
    <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-600 to-amber-500 flex items-center justify-center font-black text-white text-lg shadow-lg">
                <i data-lucide="file-check" class="w-7 h-7"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-white font-mono">{{ $loan->contract_number }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusBadge['class'] }}">
                        {{ $statusBadge['label'] }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Taxa: {{ $loan->interest_rate }}% ao mês • Vencimento Inicial: {{ $loan->first_due_date->format('d/m/Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 text-right">
            <div>
                <span class="text-xs text-slate-400">Total Financiado</span>
                <div class="text-xl font-black text-sky-400">R$ {{ number_format($loan->total_amount, 2, ',', '.') }}</div>
            </div>
            <div class="pl-4 border-l border-slate-700">
                <span class="text-xs text-slate-400">Parcelas</span>
                <div class="text-xl font-black text-white">{{ $loan->installments_count }}x de R$ {{ number_format($loan->installment_value, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Installments Schedule Table -->
    <div class="rounded-2xl bg-[#0A1324] border border-[#162540] overflow-hidden shadow-xl">
        <div class="p-6 border-b border-[#162540] flex items-center justify-between">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <i data-lucide="calendar" class="w-5 h-5 text-amber-400"></i> Cronograma de Parcelas
            </h2>
            <span class="text-xs text-slate-400">Pague com PIX para baixa instantânea</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-[#070D18] text-xs font-semibold uppercase text-slate-400 tracking-wider border-b border-[#162540]">
                    <tr>
                        <th class="px-6 py-4">Parcela</th>
                        <th class="px-6 py-4">Data de Vencimento</th>
                        <th class="px-6 py-4">Valor</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Pagamento</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#162540]">
                    @foreach($loan->installments as $inst)
                    <tr class="hover:bg-[#0D182E] transition-colors {{ $inst->status === 'pago' ? 'bg-[#070D18]/40' : '' }}">
                        <td class="px-6 py-4 font-bold text-white">
                            #{{ $inst->installment_number }} de {{ $loan->installments_count }}
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">
                            {{ $inst->due_date->format('d/m/Y') }}
                            @if($inst->isOverdue())
                                <span class="text-[10px] text-rose-400 block font-bold">VENCIDA</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-white text-base">
                            R$ {{ number_format($inst->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $inst->statusBadgeClass() }}">
                                {{ $inst->displayStatus() }}
                            </span>
                            @if($inst->paid_at)
                                <div class="text-[10px] text-emerald-400 mt-1">Pago em {{ $inst->paid_at->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($inst->status === 'pago')
                                <span class="text-xs text-emerald-400 font-bold inline-flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i> Quitado
                                </span>
                            @else
                                <a href="{{ route('client.loans.pay', $inst) }}" 
                                   class="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs uppercase tracking-wider inline-flex items-center gap-1.5 shadow-md shadow-amber-900/20 transition-all">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5"></i> Pagar via PIX
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
