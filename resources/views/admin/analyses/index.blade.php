@extends('layouts.admin')

@section('header-title', 'Análises de Crédito')

@section('content')
<style>
    .analyses-tabs { display: flex; flex-wrap: wrap; gap: 0.5rem; }
    .analyses-tab {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.6rem 0.95rem; border-radius: 0.75rem;
        font-size: 0.78rem; font-weight: 700; transition: all 0.2s;
        border: 1px solid #162540; background: #0A1324; color: #94A3B8;
    }
    .analyses-tab:hover { color: #FFFFFF; border-color: rgba(56, 189, 248, 0.35); }
    .analyses-tab.active {
        background: linear-gradient(135deg, #0284C7 0%, #1D4ED8 100%);
        color: #FFFFFF; border-color: rgba(56, 189, 248, 0.6);
        box-shadow: 0 8px 20px -6px rgba(14, 165, 233, 0.45);
    }
    .proposal-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }
    @media (min-width: 768px) {
        .proposal-metrics { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
</style>

<div class="space-y-6">

    <!-- Header & Search -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-white flex items-center gap-2.5">
                <i data-lucide="file-search" class="w-6 h-6 text-amber-400"></i>
                <span>Mesa de Análises de Crédito</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Gerencie solicitações vindas do simulador, negocie contrapropostas pelo WhatsApp e libere o acesso do cliente ao aprovar.
            </p>
        </div>

        <form method="GET" action="{{ route('admin.analyses.index') }}" class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="relative flex-1 sm:w-72">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar nome, CPF, telefone ou contrato..."
                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#0A1324] border border-[#162540] text-sm text-slate-100 placeholder-slate-500 focus:border-sky-500 focus:outline-none">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold uppercase tracking-wider transition cursor-pointer">
                Buscar
            </button>
            @if($search)
                <a href="{{ route('admin.analyses.index', ['status' => $status]) }}" class="px-3.5 py-2.5 rounded-xl bg-[#0A1324] border border-[#162540] text-slate-300 hover:text-white text-xs font-semibold text-center">
                    Limpar
                </a>
            @endif
        </form>
    </div>

    <!-- Filter Tabs -->
    <div class="analyses-tabs">
        <a href="{{ route('admin.analyses.index', ['status' => 'em_analise', 'search' => $search]) }}"
           class="analyses-tab {{ $status === 'em_analise' ? 'active' : '' }}">
            <span>Em Análise</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-black {{ $status === 'em_analise' ? 'bg-white text-blue-900' : 'bg-amber-500/20 text-amber-300' }}">
                {{ $counts['em_analise'] }}
            </span>
        </a>

        <a href="{{ route('admin.analyses.index', ['status' => 'aprovado', 'search' => $search]) }}"
           class="analyses-tab {{ $status === 'aprovado' ? 'active' : '' }}">
            <span>Aprovadas (Site)</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-black {{ $status === 'aprovado' ? 'bg-white text-blue-900' : 'bg-emerald-500/20 text-emerald-300' }}">
                {{ $counts['aprovado'] }}
            </span>
        </a>

        <a href="{{ route('admin.analyses.index', ['status' => 'rejeitado', 'search' => $search]) }}"
           class="analyses-tab {{ $status === 'rejeitado' ? 'active' : '' }}">
            <span>Reprovadas</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-black {{ $status === 'rejeitado' ? 'bg-white text-blue-900' : 'bg-rose-500/20 text-rose-300' }}">
                {{ $counts['rejeitado'] }}
            </span>
        </a>

        <a href="{{ route('admin.analyses.index', ['status' => 'todos', 'search' => $search]) }}"
           class="analyses-tab {{ $status === 'todos' ? 'active' : '' }}">
            <span>Todas</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-black {{ $status === 'todos' ? 'bg-white text-blue-900' : 'bg-slate-700 text-slate-200' }}">
                {{ $counts['todos'] }}
            </span>
        </a>
    </div>

    <!-- Proposals List (Responsive Cards for Desktop & Mobile) -->
    @if($proposals->isEmpty())
        <div class="p-10 rounded-2xl bg-[#0A1324] border border-[#162540] text-center space-y-3">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-800/70 flex items-center justify-center text-slate-400">
                <i data-lucide="inbox" class="w-7 h-7"></i>
            </div>
            <h3 class="text-base font-bold text-white">Nenhuma proposta encontrada nesta fila</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto">
                Quando clientes preencherem o formulário "Contratar Agora via PIX" na página inicial, as solicitações aparecerão aqui automaticamente para análise.
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($proposals as $proposal)
                @php
                    $badge = $proposal->statusBadge();
                    $client = $proposal->client;
                    $income = (float) ($client->monthly_income ?? 0);
                    $ratio = $income > 0 ? round(((float) $proposal->installment_value / $income) * 100, 1) : null;
                    $isPortalUnlocked = (bool) ($client->user?->is_active ?? false);
                @endphp

                <div class="p-5 sm:p-6 rounded-2xl bg-[#0A1324] border {{ $proposal->status === 'em_analise' ? 'border-amber-500/30' : 'border-[#162540]' }} shadow-lg space-y-4">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-1.5 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-bold px-2.5 py-0.5 rounded-lg bg-[#070D18] border border-[#1E3252] text-sky-300">
                                    {{ $proposal->contract_number }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                                @if($isPortalUnlocked)
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Acesso Liberado
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                        Acesso Bloqueado
                                    </span>
                                @endif
                            </div>

                            <h2 class="text-lg font-extrabold text-white break-words">
                                {{ $client->name }}
                            </h2>

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400">
                                <span class="font-mono">CPF/CNPJ: {{ $client->formattedDocument() }}</span>
                                <span>•</span>
                                <span class="font-mono text-emerald-400">{{ $client->formattedPhone() }}</span>
                                <span>•</span>
                                <span>{{ $client->city ?? '-' }}/{{ $client->state ?? '-' }}</span>
                                <span>•</span>
                                <span>Enviado em {{ $proposal->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            @if(in_array($proposal->status, ['aprovado', 'ativo', 'quitado']))
                                <a href="{{ $proposal->whatsappApprovalUrl() }}" target="_blank" rel="noopener noreferrer"
                                   class="flex-1 sm:flex-initial justify-center px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-1.5 transition shadow">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    <span>Comunicar Aprovação</span>
                                </a>
                            @else
                                <a href="{{ $proposal->whatsappContactUrl() }}" target="_blank" rel="noopener noreferrer"
                                   class="flex-1 sm:flex-initial justify-center px-3.5 py-2.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 text-xs font-bold flex items-center gap-1.5 transition">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    <span>WhatsApp</span>
                                </a>
                            @endif

                            <a href="{{ route('admin.analyses.show', $proposal) }}"
                               class="flex-1 sm:flex-initial justify-center px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white text-xs font-bold uppercase tracking-wider flex items-center gap-1.5 transition shadow">
                                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                                <span>{{ $proposal->status === 'em_analise' ? 'Analisar & Decidir' : 'Ver Análise' }}</span>
                            </a>
                        </div>
                    </div>

                    <!-- Metrics Row -->
                    <div class="proposal-metrics pt-3 border-t border-[#162540]">
                        <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                            <span class="text-[11px] text-slate-400 block">Valor Solicitado</span>
                            <strong class="text-sm font-black text-amber-400">R$ {{ number_format($proposal->amount, 2, ',', '.') }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                            <span class="text-[11px] text-slate-400 block">Parcelamento</span>
                            <strong class="text-sm font-bold text-white">{{ $proposal->installments_count }}x de R$ {{ number_format($proposal->installment_value, 2, ',', '.') }}</strong>
                        </div>
                        <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                            <span class="text-[11px] text-slate-400 block">Renda / Comprometimento</span>
                            <strong class="text-sm font-bold {{ $ratio !== null && $ratio > 45 ? 'text-rose-400' : 'text-sky-400' }}">
                                R$ {{ number_format($income, 2, ',', '.') }}
                                @if($ratio !== null)
                                    ({{ $ratio }}%)
                                @endif
                            </strong>
                        </div>
                        <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                            <span class="text-[11px] text-slate-400 block">Chave PIX ({{ strtoupper($proposal->pix_key_type ?? '-') }})</span>
                            <strong class="text-xs font-mono text-slate-200 break-all">{{ $proposal->pix_key }}</strong>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-2">
            {{ $proposals->links() }}
        </div>
    @endif

</div>
@endsection
