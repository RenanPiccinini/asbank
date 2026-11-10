@extends('layouts.admin')

@section('header-title', 'Análise • ' . $loan->contract_number)

@section('content')
@php
    $badge = $loan->statusBadge();
    $isApproved = in_array($loan->status, ['aprovado', 'ativo', 'quitado']);
    $isRejected = $loan->status === 'rejeitado';
    $isPending = $loan->status === 'em_analise';
    $isLockedFinancial = $loan->disbursement_status === 'enviado' || $loan->paidInstallmentsCount() > 0;
    $isPortalUnlocked = (bool) ($client->user?->is_active ?? false);
@endphp

<style>
    .analysis-main-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
        align-items: start;
    }
    @media (min-width: 1024px) {
        .analysis-main-grid {
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
        }
    }
    .dossier-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.85rem;
    }
    @media (min-width: 640px) {
        .dossier-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dossier-span-2 { grid-column: span 2 / span 2; }
    }
    .summary-preview-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }
    @media (min-width: 640px) {
        .summary-preview-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
</style>

<div class="space-y-6">

    <!-- Top Back & Quick Link -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('admin.analyses.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Voltar para Análises de Crédito</span>
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.clients.show', $client) }}" class="px-3.5 py-2 rounded-xl bg-[#0A1324] border border-[#162540] text-xs font-semibold text-slate-300 hover:text-white transition flex items-center gap-1.5">
                <i data-lucide="user" class="w-4 h-4 text-sky-400"></i>
                <span>Ficha do Cliente</span>
            </a>
            @if($isApproved)
                <a href="{{ route('admin.loans.show', $loan) }}" class="px-3.5 py-2 rounded-xl bg-sky-600/20 border border-sky-500/30 text-xs font-bold text-sky-300 hover:bg-sky-600/30 transition flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>Abrir Contrato & Enviar PIX</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Auto WhatsApp Prompt after Save & Send -->
    @if(session('open_whatsapp_url'))
        <div class="p-4 sm:p-5 rounded-2xl bg-emerald-500/15 border border-emerald-500/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <i data-lucide="message-circle" class="w-6 h-6 text-emerald-400 shrink-0 mt-0.5"></i>
                <div>
                    <h3 class="text-sm font-bold text-white">Contraproposta salva e pronta para envio!</h3>
                    <p class="text-xs text-emerald-200/80 mt-0.5">Caso o navegador tenha bloqueado o pop-up automático, clique no botão ao lado para abrir o WhatsApp de {{ $client->name }}.</p>
                </div>
            </div>
            <a href="{{ session('open_whatsapp_url') }}" target="_blank" rel="noopener noreferrer"
               class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 shrink-0 shadow">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Abrir WhatsApp Agora</span>
            </a>
        </div>
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                window.open(@json(session('open_whatsapp_url')), '_blank', 'noopener,noreferrer');
            });
        </script>
    @endif

    <!-- Overview Hero -->
    <div class="p-5 sm:p-6 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-xl space-y-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1.5 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-sm font-black text-white px-2.5 py-0.5 rounded-lg bg-[#070D18] border border-[#1E3252]">
                        {{ $loan->contract_number }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
                        {{ $badge['label'] }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isPortalUnlocked ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                        {{ $isPortalUnlocked ? 'Portal Liberado' : 'Acesso ao Portal Bloqueado' }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-black text-white break-words">{{ $client->name }}</h1>

                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-400">
                    <span class="font-mono">CPF/CNPJ: {{ $client->formattedDocument() }}</span>
                    <span>•</span>
                    <span class="font-mono text-emerald-400">{{ $client->formattedPhone() }}</span>
                    <span>•</span>
                    <span>{{ $client->email }}</span>
                    <span>•</span>
                    <span>Solicitado em {{ $loan->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <!-- Dynamic WhatsApp Contact Button Always Visible -->
            <a href="{{ $isApproved ? $loan->whatsappApprovalUrl() : ($isRejected ? $loan->whatsappRejectionUrl() : $loan->whatsappContactUrl()) }}"
               target="_blank" rel="noopener noreferrer"
               class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg transition shrink-0">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>
                    @if($isApproved)
                        Comunicar Aprovação no WhatsApp
                    @elseif($isRejected)
                        Comunicar Resultado no WhatsApp
                    @else
                        Contatar Cliente no WhatsApp
                    @endif
                </span>
            </a>
        </div>

        <div class="summary-preview-grid pt-4 border-t border-[#162540]">
            <div class="p-3.5 rounded-xl bg-[#070D18] border border-[#162540]">
                <span class="text-[11px] text-slate-400 block">Valor da Proposta</span>
                <strong class="text-lg font-black text-amber-400">R$ {{ number_format($loan->amount, 2, ',', '.') }}</strong>
            </div>
            <div class="p-3.5 rounded-xl bg-[#070D18] border border-[#162540]">
                <span class="text-[11px] text-slate-400 block">Parcelamento ({{ $loan->interest_rate }}% a.m.)</span>
                <strong class="text-lg font-bold text-white">{{ $loan->installments_count }}x de R$ {{ number_format($loan->installment_value, 2, ',', '.') }}</strong>
            </div>
            <div class="p-3.5 rounded-xl bg-[#070D18] border border-[#162540]">
                <span class="text-[11px] text-slate-400 block">Comprometimento de Renda</span>
                <strong class="text-lg font-bold {{ $incomeRatio !== null && $incomeRatio > 45 ? 'text-rose-400' : ($incomeRatio !== null && $incomeRatio > 30 ? 'text-amber-300' : 'text-emerald-400') }}">
                    {{ $incomeRatio !== null ? $incomeRatio . '% da renda' : 'Não informada' }}
                </strong>
            </div>
            <div class="p-3.5 rounded-xl bg-[#070D18] border border-[#162540]">
                <span class="text-[11px] text-slate-400 block">Score Estimado</span>
                <strong class="text-lg font-bold text-sky-400">{{ $loan->credit_score ?? '-' }} pts</strong>
            </div>
        </div>
    </div>

    <div class="analysis-main-grid">

        <!-- LEFT COLUMN: Complete Applicant Dossier -->
        <div class="space-y-6">

            <!-- Dados Pessoais e Financeiros -->
            <div class="p-5 sm:p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-4">
                <h2 class="text-base font-bold text-white flex items-center gap-2 pb-3 border-b border-[#162540]">
                    <i data-lucide="user-check" class="w-5 h-5 text-sky-400"></i>
                    <span>Dossiê Completo do Solicitante</span>
                </h2>

                <div class="dossier-grid text-sm">
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">CPF / CNPJ</span>
                        <strong class="font-mono text-white">{{ $client->formattedDocument() }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">RG</span>
                        <strong class="text-white">{{ $client->rg ?: 'Não informado' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Data de Nascimento</span>
                        <strong class="text-white">{{ $client->birth_date ? $client->birth_date->format('d/m/Y') : 'Não informado' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">WhatsApp</span>
                        <strong class="font-mono text-emerald-400">{{ $client->formattedPhone() }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Ocupação / Profissão</span>
                        <strong class="text-white">{{ $client->occupation ?: 'Não informado' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Renda Mensal Declarada</span>
                        <strong class="text-emerald-400">R$ {{ number_format((float) $client->monthly_income, 2, ',', '.') }}</strong>
                    </div>
                    <div class="dossier-span-2 p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Finalidade do Empréstimo</span>
                        <strong class="text-sky-300">{{ $loan->loan_purpose ?: 'Não especificada' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Endereço Completo -->
            <div class="p-5 sm:p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-4">
                <h2 class="text-base font-bold text-white flex items-center gap-2 pb-3 border-b border-[#162540]">
                    <i data-lucide="map-pin" class="w-5 h-5 text-sky-400"></i>
                    <span>Endereço Completo</span>
                </h2>

                <div class="dossier-grid text-sm">
                    <div class="dossier-span-2 p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Logradouro</span>
                        <strong class="text-white">{{ $client->address }}, nº {{ $client->number }} {{ $client->complement ? '(' . $client->complement . ')' : '' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Bairro</span>
                        <strong class="text-white">{{ $client->neighborhood ?: '-' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Cidade / UF</span>
                        <strong class="text-white">{{ $client->city ?: '-' }} / {{ $client->state ?: '-' }}</strong>
                    </div>
                    <div class="dossier-span-2 p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">CEP</span>
                        <strong class="font-mono text-white">{{ $client->zip_code ?: '-' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Dados Bancários & PIX -->
            <div class="p-5 sm:p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-4">
                <h2 class="text-base font-bold text-white flex items-center gap-2 pb-3 border-b border-[#162540]">
                    <i data-lucide="qr-code" class="w-5 h-5 text-amber-400"></i>
                    <span>Conta Bancária & Chave PIX de Recebimento</span>
                </h2>

                <div class="dossier-grid text-sm">
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Banco / Instituição</span>
                        <strong class="text-white">{{ $client->bank_name ?: 'Não informado' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Agência / Conta</span>
                        <strong class="font-mono text-white">{{ $client->bank_agency ?: '-' }} / {{ $client->bank_account ?: '-' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Tipo da Chave PIX</span>
                        <strong class="uppercase text-amber-400">{{ $loan->pix_key_type }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-[#070D18] border border-[#162540]">
                        <span class="text-[11px] text-slate-500 block">Chave PIX Cadastrada</span>
                        <strong class="font-mono text-white break-all">{{ $loan->pix_key }}</strong>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN: Decision Actions & Proposal Editor -->
        <div class="space-y-6">

            <!-- Decision & Communication Panel -->
            <div class="p-5 sm:p-6 rounded-2xl bg-[#0A1324] border {{ $isApproved ? 'border-emerald-500/40' : ($isRejected ? 'border-rose-500/40' : 'border-amber-500/40') }} space-y-4 shadow-xl">
                <h2 class="text-base font-bold text-white flex items-center gap-2 pb-3 border-b border-[#162540]">
                    <i data-lucide="shield-check" class="w-5 h-5 {{ $isApproved ? 'text-emerald-400' : ($isRejected ? 'text-rose-400' : 'text-amber-400') }}"></i>
                    <span>Central de Decisão & WhatsApp</span>
                </h2>

                @if($isPending)
                    <p class="text-xs text-slate-300 leading-relaxed">
                        A conta deste cliente está atualmente <strong class="text-amber-300">bloqueada</strong>. Ao aprovar, o acesso ao Portal será liberado, o contrato/parcelas serão gerados e o botão de aprovação via WhatsApp será habilitado.
                    </p>

                    <div class="space-y-3">
                        <a href="{{ $loan->whatsappContactUrl() }}" target="_blank" rel="noopener noreferrer"
                           class="w-full py-3 px-4 rounded-xl bg-[#070D18] border border-emerald-500/40 hover:bg-emerald-500/10 text-emerald-300 text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Conversar no WhatsApp com {{ explode(' ', $client->name)[0] }}</span>
                        </a>

                        <form action="{{ route('admin.analyses.approve', $loan) }}" method="POST"
                              onsubmit="return confirmAction(event, 'Aprovar Proposta e Liberar Acesso?', 'O login de {{ addslashes($client->name) }} será desbloqueado e o contrato {{ $loan->contract_number }} (R$ {{ number_format($loan->amount, 2, ',', '.') }}) será gerado.', 'check-circle', 'emerald')">
                            @csrf
                            <button type="submit"
                                    class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg transition cursor-pointer">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                <span>Aprovar Proposta & Liberar Acesso</span>
                            </button>
                        </form>

                        <form action="{{ route('admin.analyses.reject', $loan) }}" method="POST" class="pt-3 border-t border-[#162540] space-y-2.5"
                              onsubmit="return confirmAction(event, 'Reprovar esta Proposta?', 'O acesso do cliente permanecerá bloqueado e a proposta será marcada como Rejeitada.', 'x-circle', 'rose')">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-400" for="rejection_reason">Motivo da Reprovação (Opcional)</label>
                            <textarea name="rejection_reason" id="rejection_reason" rows="2"
                                      placeholder="Ex: Renda incompatível com o valor solicitado no momento..."
                                      class="w-full px-3 py-2 rounded-xl bg-[#070D18] border border-[#162540] text-xs text-slate-200 focus:border-rose-500 focus:outline-none">{{ old('rejection_reason') }}</textarea>
                            <button type="submit"
                                    class="w-full py-2.5 px-4 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-300 text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition cursor-pointer">
                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                <span>Reprovar Proposta</span>
                            </button>
                        </form>
                    </div>

                @elseif($isApproved)
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-200 space-y-1">
                        <div class="font-bold text-emerald-300 flex items-center gap-1.5">
                            <i data-lucide="unlock" class="w-4 h-4"></i>
                            <span>Proposta Aprovada & Portal Liberado</span>
                        </div>
                        <p class="text-emerald-200/80">
                            O acesso do cliente está ativo e as {{ $loan->installments_count }} parcelas já foram emitidas.
                            @if($loan->reviewed_at)
                                Aprovada em {{ $loan->reviewed_at->format('d/m/Y H:i') }}.
                            @endif
                        </p>
                    </div>

                    <div class="space-y-2.5">
                        <a href="{{ $loan->whatsappApprovalUrl() }}" target="_blank" rel="noopener noreferrer"
                           class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg transition">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Comunicar Aprovação por WhatsApp</span>
                        </a>

                        <a href="{{ route('admin.loans.show', $loan) }}"
                           class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow transition">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                            <span>Ver Contrato & Realizar PIX</span>
                        </a>
                    </div>

                @elseif($isRejected)
                    <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-200 space-y-1">
                        <div class="font-bold text-rose-300">Proposta Reprovada</div>
                        <p class="text-rose-200/80">{{ $loan->rejection_reason }}</p>
                    </div>

                    <div class="space-y-2.5">
                        <a href="{{ $loan->whatsappRejectionUrl() }}" target="_blank" rel="noopener noreferrer"
                           class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            <span>Comunicar Resultado no WhatsApp</span>
                        </a>

                        <form action="{{ route('admin.analyses.reopen', $loan) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full py-2.5 px-4 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-300 text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-2 transition cursor-pointer">
                                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                                <span>Reabrir para Nova Análise</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Proposal / Counter-Offer Editor -->
            <div class="p-5 sm:p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-4">
                <h2 class="text-base font-bold text-white flex items-center gap-2 pb-3 border-b border-[#162540]">
                    <i data-lucide="pencil" class="w-5 h-5 text-sky-400"></i>
                    <span>Editar Proposta / Enviar Contraproposta</span>
                </h2>

                @if($isLockedFinancial)
                    <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-200">
                        Os valores desta proposta não podem mais ser editados pois o PIX já foi desembolsado ou há parcelas pagas.
                    </div>
                @else
                    <form action="{{ route('admin.analyses.update-proposal', $loan) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="dossier-grid">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Valor Aprovado (R$)</label>
                                <input type="number" step="0.01" min="0.01" max="1000000" name="amount" id="editAmount"
                                       value="{{ old('amount', $loan->amount) }}" required oninput="recalcAdminProposal()"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm font-bold text-amber-300 focus:border-sky-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Taxa de Juros (% a.m.)</label>
                                <input type="number" step="0.01" min="0" max="100" name="interest_rate" id="editRate"
                                       value="{{ old('interest_rate', $loan->interest_rate) }}" required oninput="recalcAdminProposal()"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nº de Parcelas</label>
                                <input type="number" min="1" max="72" name="installments_count" id="editInstallments"
                                       value="{{ old('installments_count', $loan->installments_count) }}" required oninput="recalcAdminProposal()"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">1º Vencimento</label>
                                <input type="date" name="first_due_date"
                                       value="{{ old('first_due_date', optional($loan->first_due_date)->format('Y-m-d')) }}" required
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Score de Crédito</label>
                                <input type="number" min="300" max="1000" name="credit_score"
                                       value="{{ old('credit_score', $loan->credit_score) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tipo Chave PIX</label>
                                <select name="pix_key_type" class="w-full px-3.5 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 focus:outline-none">
                                    @php $curPixType = old('pix_key_type', $loan->pix_key_type); @endphp
                                    <option value="cpf" {{ $curPixType === 'cpf' ? 'selected' : '' }}>CPF</option>
                                    <option value="cnpj" {{ $curPixType === 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                                    <option value="phone" {{ $curPixType === 'phone' ? 'selected' : '' }}>Telefone</option>
                                    <option value="email" {{ $curPixType === 'email' ? 'selected' : '' }}>E-mail</option>
                                    <option value="random" {{ $curPixType === 'random' ? 'selected' : '' }}>Aleatória</option>
                                </select>
                            </div>

                            <div class="dossier-span-2">
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Chave PIX de Recebimento</label>
                                <input type="text" name="pix_key" value="{{ old('pix_key', $loan->pix_key) }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm font-mono text-white focus:border-sky-500 focus:outline-none">
                            </div>

                            <div class="dossier-span-2">
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Parecer / Anotações Internas</label>
                                <textarea name="credit_analysis_notes" rows="2"
                                          class="w-full px-3.5 py-2 rounded-xl bg-[#070D18] border border-[#162540] text-xs text-slate-200 focus:border-sky-500 focus:outline-none">{{ old('credit_analysis_notes', $loan->credit_analysis_notes) }}</textarea>
                            </div>
                        </div>

                        <!-- Live recalculation box -->
                        <div class="p-3.5 rounded-xl bg-[#070D18] border border-sky-500/25 flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div>
                                <span class="text-slate-400">Nova Parcela Estimada:</span>
                                <strong id="adminPreviewParcel" class="text-amber-400 font-black ml-1">
                                    {{ $loan->installments_count }}x de R$ {{ number_format($loan->installment_value, 2, ',', '.') }}
                                </strong>
                            </div>
                            <div>
                                <span class="text-slate-400">Total:</span>
                                <strong id="adminPreviewTotal" class="text-sky-400 font-bold ml-1">
                                    R$ {{ number_format($loan->total_amount, 2, ',', '.') }}
                                </strong>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-2.5 pt-1">
                            <button type="submit" name="action" value="save"
                                    class="flex-1 py-2.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>Salvar Condições</span>
                            </button>

                            <button type="submit" name="action" value="save_and_whatsapp"
                                    class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold uppercase tracking-wider flex items-center justify-center gap-1.5 transition cursor-pointer">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>Salvar & Enviar via WhatsApp</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>

        </div>
    </div>

</div>

<script>
    function recalcAdminProposal() {
        const amount = Math.max(0, parseFloat(document.getElementById('editAmount')?.value) || 0);
        const rate = Math.max(0, parseFloat(document.getElementById('editRate')?.value) || 0);
        const count = Math.max(1, parseInt(document.getElementById('editInstallments')?.value) || 1);

        const total = amount + (amount * (rate / 100) * count);
        const parcel = total / count;

        const fmt = v => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        const pEl = document.getElementById('adminPreviewParcel');
        const tEl = document.getElementById('adminPreviewTotal');
        if (pEl) pEl.innerText = count + 'x de ' + fmt(parcel);
        if (tEl) tEl.innerText = fmt(total);
    }
</script>
@endsection
