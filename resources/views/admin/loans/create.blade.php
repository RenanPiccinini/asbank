@extends('layouts.admin')

@section('header-title', 'Cadastrar Novo Empréstimo')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.loans.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar para lista de empréstimos
        </a>
    </div>

    <form method="POST" action="{{ route('admin.loans.store') }}" id="loanForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        <!-- Main Form Columns -->
        <div class="lg:col-span-2 space-y-6">
            <div class="p-8 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-6">
                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2 mb-1">
                        <i data-lucide="banknote" class="w-5 h-5 text-amber-400"></i> Condições da Operação de Crédito
                    </h2>
                    <p class="text-xs text-slate-400">Preencha o cliente e as condições. O sistema calculará o parcelamento e o contrato automaticamente.</p>
                </div>

                <!-- Client Selector -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Cliente Mutuário *</label>
                    <select name="client_id" id="client_id" required onchange="updateClientPix(this)"
                            class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                        <option value="">Selecione o cliente cadastrado...</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" 
                                    data-pix="{{ $c->pix_key }}" 
                                    data-pixtype="{{ $c->pix_key_type }}"
                                    data-limit="{{ $c->credit_limit }}"
                                    {{ (old('client_id') == $c->id || $selectedClientId == $c->id) ? 'selected' : '' }}>
                                {{ $c->name }} (CPF: {{ $c->formattedDocument() }}) - Limite: R$ {{ number_format($c->credit_limit, 2, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Amount, Rate & Installments -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Valor Solicitado (R$) *</label>
                        <input type="number" step="0.01" min="0.01" max="1000000" name="amount" id="amount" value="{{ old('amount', '5000.00') }}" required 
                               oninput="calculateSimulation()"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white font-bold focus:border-sky-500">
                        @error('amount') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Taxa de Juros (% a.m.) *</label>
                        <input type="number" step="0.01" min="0" max="50" name="interest_rate" id="interest_rate" value="{{ old('interest_rate', '2.50') }}" required 
                               oninput="calculateSimulation()"
                               class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                        @error('interest_rate') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nº de Parcelas *</label>
                        <select name="installments_count" id="installments_count" required onchange="calculateSimulation()"
                                class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                            @for($i = 1; $i <= 48; $i++)
                                <option value="{{ $i }}" {{ old('installments_count', '6') == $i ? 'selected' : '' }}>{{ $i }}x</option>
                            @endfor
                        </select>
                        @error('installments_count') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- First Due Date & PIX Key -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Data do 1º Vencimento *</label>
                        <input type="date" name="first_due_date" id="first_due_date" 
                               value="{{ old('first_due_date', now()->addMonth()->format('Y-m-d')) }}" required 
                               class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                        @error('first_due_date') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Score de Análise de Crédito</label>
                        <input type="number" min="300" max="1000" name="credit_score" value="{{ old('credit_score', '850') }}" 
                               class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 font-mono">
                    </div>
                </div>

                <!-- Destination PIX Key Override (Optional) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Chave PIX para Desembolso (MisticPay Cash-Out)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <select name="pix_key_type" id="pix_key_type" class="px-3 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-xs text-white">
                            <option value="cpf">CPF</option>
                            <option value="cnpj">CNPJ</option>
                            <option value="email">E-mail</option>
                            <option value="phone">Telefone</option>
                            <option value="random">Chave Aleatória (EVP)</option>
                        </select>
                        <input type="text" name="pix_key" id="pix_key" value="{{ old('pix_key') }}" 
                               placeholder="Preenchida automaticamente pelo cadastro do cliente"
                               class="sm:col-span-2 px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white font-mono focus:border-sky-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Parecer da Análise de Crédito</label>
                    <textarea name="credit_analysis_notes" rows="2" class="w-full px-4 py-2 rounded-xl bg-[#070D18] border border-[#162540] text-xs text-white focus:border-sky-500" placeholder="Aprovado com base no histórico de pontualidade e renda declarada.">{{ old('credit_analysis_notes', 'Score interno ASBANK aprovado com limite favorável para liberação imediata via PIX.') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Simulation Summary Column -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-gradient-to-b from-[#0F1D38] to-[#0A1324] border border-sky-500/30 sticky top-28 shadow-xl">
                <div class="flex items-center gap-2 text-amber-400 font-bold text-xs uppercase tracking-wider mb-4 pb-2 border-b border-sky-500/20">
                    <i data-lucide="calculator" class="w-4 h-4"></i> Simulador em Tempo Real
                </div>

                <div class="space-y-4">
                    <div>
                        <span class="text-xs text-slate-400">Valor Solicitado</span>
                        <div class="text-xl font-bold text-white" id="sim_amount">R$ 5.000,00</div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400">Juros Totais Calculados</span>
                        <div class="text-base font-semibold text-amber-300" id="sim_interest">+ R$ 750,00</div>
                    </div>

                    <div class="pt-3 border-t border-slate-700/60">
                        <span class="text-xs text-slate-400">Total a Pagar com Juros</span>
                        <div class="text-2xl font-black text-sky-400 tracking-tight" id="sim_total">R$ 5.750,00</div>
                    </div>

                    <div class="p-4 rounded-xl bg-[#070D18]/90 border border-amber-500/30 text-center">
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Valor da Parcela Estimada</span>
                        <div class="text-2xl font-black text-amber-400" id="sim_parcel">6x de R$ 958,33</div>
                    </div>

                    <div class="text-[11px] text-slate-400 leading-relaxed pt-2">
                        ✓ O contrato nº <strong>ASB-{{ date('Y') }}-AUTO</strong> e todas as parcelas individuais serão geradas automaticamente no banco de dados.
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-sm uppercase tracking-wider shadow-lg shadow-amber-900/30 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-5 h-5"></i>
                        <span>Confirmar & Gerar Contrato</span>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    function updateClientPix(selectElement) {
        const option = selectElement.options[selectElement.selectedIndex];
        if (option && option.dataset.pix) {
            document.getElementById('pix_key').value = option.dataset.pix;
            document.getElementById('pix_key_type').value = option.dataset.pixtype || 'cpf';
        }
    }

    function calculateSimulation() {
        const amount = parseFloat(document.getElementById('amount').value) || 0;
        const rate = parseFloat(document.getElementById('interest_rate').value) || 0;
        const count = parseInt(document.getElementById('installments_count').value) || 1;

        const totalInterest = amount * ((rate / 100) * count);
        const totalAmount = amount + totalInterest;
        const parcelValue = totalAmount / count;

        document.getElementById('sim_amount').innerText = 'R$ ' + amount.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('sim_interest').innerText = '+ R$ ' + totalInterest.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('sim_total').innerText = 'R$ ' + totalAmount.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('sim_parcel').innerText = count + 'x de R$ ' + parcelValue.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Trigger on page load
    document.addEventListener('DOMContentLoaded', () => {
        calculateSimulation();
        const clientSelect = document.getElementById('client_id');
        if (clientSelect.value) {
            updateClientPix(clientSelect);
        }
    });
</script>
@endpush

