@extends('layouts.admin')

@section('header-title', 'Dashboard Financeiro')

@section('content')
<div class="space-y-6">

    <!-- Top Alert if MisticPay in simulation mode -->
    @if($gatewayStatus['is_mock'])
    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-300 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-amber-500/20 text-amber-400">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-sm font-bold text-white">Gateway MisticPay operando em Modo de Simulação</p>
                <p class="text-xs text-amber-400/80">Você pode testar a criação de cobranças e envios de PIX livremente. Para ativar o ambiente real, insira as chaves de API.</p>
            </div>
        </div>
        <a href="{{ route('admin.settings.misticpay') }}" class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs uppercase tracking-wider hover:bg-amber-400 transition-colors whitespace-nowrap">
            Configurar Chaves
        </a>
    </div>
    @endif

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Emprestado -->
        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540] hover:border-sky-500/30 transition-all shadow-sm group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400">Total Emprestado</span>
                <div class="p-2.5 rounded-xl bg-sky-500/10 text-sky-400 group-hover:bg-sky-500/20 transition-colors">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white tracking-tight">
                R$ {{ number_format($totalEmprestado, 2, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-sky-400 font-medium flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                <span>Contratos aprovados/ativos</span>
            </div>
        </div>

        <!-- Total Recebido via PIX -->
        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540] hover:border-emerald-500/30 transition-all shadow-sm group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400">Total Recebido (PIX)</span>
                <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-400 group-hover:bg-emerald-500/20 transition-colors">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-400 tracking-tight">
                R$ {{ number_format($totalRecebido, 2, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-emerald-400/80 font-medium flex items-center gap-1">
                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                <span>Parcelas liquidadas</span>
            </div>
        </div>

        <!-- Total a Receber -->
        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540] hover:border-amber-500/30 transition-all shadow-sm group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400">Total a Receber</span>
                <div class="p-2.5 rounded-xl bg-amber-500/10 text-amber-400 group-hover:bg-amber-500/20 transition-colors">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-300 tracking-tight">
                R$ {{ number_format($totalAReceber, 2, ',', '.') }}
            </div>
            <div class="mt-2 text-xs text-amber-400/80 font-medium flex items-center gap-1">
                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                <span>Parcelas futuras / em aberto</span>
            </div>
        </div>

        <!-- Clientes & Contratos -->
        <div class="p-5 rounded-2xl bg-[#0A1324] border border-[#162540] hover:border-blue-500/30 transition-all shadow-sm group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400">Clientes Ativos</span>
                <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-400 group-hover:bg-blue-500/20 transition-colors">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-white tracking-tight">
                {{ $totalClientes }} <span class="text-sm font-normal text-slate-400">clientes</span>
            </div>
            <div class="mt-2 text-xs text-blue-400 font-medium flex items-center gap-1">
                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                <span>{{ $totalEmprestimos }} contratos registrados</span>
            </div>
        </div>
    </div>

    <!-- Extrato Summary Strip -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-[#0A1324] via-[#0F1E3A] to-[#0A1324] border border-[#162540] flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="p-3.5 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30">
                <i data-lucide="zap" class="w-7 h-7"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-white">Extrato de Fluxo PIX MisticPay</h2>
                <p class="text-xs text-slate-400">Resumo consolidado de todas as entradas e saídas processadas</p>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <p class="text-xs text-slate-400">Total Enviado (Cash-Out)</p>
                <p class="text-base font-bold text-rose-400">- R$ {{ number_format($totalEnviosPix, 2, ',', '.') }}</p>
            </div>
            <div class="h-8 w-px bg-slate-700"></div>
            <div class="text-right">
                <p class="text-xs text-slate-400">Total Recebido (Cash-In)</p>
                <p class="text-base font-bold text-emerald-400">+ R$ {{ number_format($totalRecebimentosPix, 2, ',', '.') }}</p>
            </div>
            <a href="{{ route('admin.statement.index') }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold text-xs transition-colors flex items-center gap-1.5 shadow-md">
                Ver Extrato Completo <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>

    <!-- Two Columns: Recent Loans & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Loans -->
        <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540]">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="banknote" class="w-5 h-5 text-amber-400"></i>
                    <h2 class="text-base font-bold text-white">Últimos Empréstimos</h2>
                </div>
                <a href="{{ route('admin.loans.index') }}" class="text-xs font-semibold text-sky-400 hover:text-sky-300">
                    Ver todos
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentLoans as $loan)
                    @php $badge = $loan->statusBadge(); @endphp
                    <div class="p-4 rounded-xl bg-[#070D18] border border-[#162540] flex items-center justify-between hover:border-slate-700 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-600/10 border border-sky-500/20 flex items-center justify-center font-bold text-sky-400 text-xs">
                                {{ $loan->installments_count }}x
                            </div>
                            <div>
                                <a href="{{ route('admin.loans.show', $loan) }}" class="text-sm font-bold text-white hover:text-sky-400 transition-colors">
                                    {{ $loan->client->name }}
                                </a>
                                <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                                    <span>{{ $loan->contract_number }}</span>
                                    <span>•</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] border {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-sm font-bold text-white">
                                R$ {{ number_format($loan->amount, 2, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">
                                @if($loan->disbursement_status === 'enviado')
                                    <span class="text-emerald-400 flex items-center justify-end gap-1">
                                        <i data-lucide="check" class="w-3 h-3"></i> PIX Enviado
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.loans.send-pix', $loan) }}" onsubmit='return confirm("Confirma o envio do PIX de R$ {{ number_format($loan->amount, 2, ',', '.') }} para a chave de {{ $loan->client->name }}?");'>
                                        @csrf
                                        <button type="submit" class="text-amber-400 hover:text-amber-300 font-bold hover:underline flex items-center justify-end gap-1">
                                            <i data-lucide="send" class="w-3 h-3"></i> Enviar PIX
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500 text-sm">
                        Nenhum empréstimo cadastrado ainda.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Financial Transactions (Extrato) -->
        <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540]">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="arrow-left-right" class="w-5 h-5 text-sky-400"></i>
                    <h2 class="text-base font-bold text-white">Extrato Recente</h2>
                </div>
                <a href="{{ route('admin.statement.index') }}" class="text-xs font-semibold text-sky-400 hover:text-sky-300">
                    Ver extrato
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentTransactions as $tx)
                    @php $typeBadge = $tx->typeBadge(); @endphp
                    <div class="p-4 rounded-xl bg-[#070D18] border border-[#162540] flex items-center justify-between hover:border-slate-700 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $tx->type === 'cash_out' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }} flex items-center justify-center font-bold">
                                <i data-lucide="{{ $tx->type === 'cash_out' ? 'arrow-up-right' : 'arrow-down-left' }}" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">
                                    {{ $tx->counterparty_name ?: ($tx->client->name ?? 'Transação PIX') }}
                                </p>
                                <p class="text-xs text-slate-400">
                                    {{ $tx->category }} • {{ $tx->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-sm font-bold {{ $typeBadge['color'] }}">
                                {{ $typeBadge['sign'] }} R$ {{ number_format($tx->amount, 2, ',', '.') }}
                            </span>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ strtoupper($tx->status) }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-500 text-sm">
                        Nenhuma movimentação registrada no extrato.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
