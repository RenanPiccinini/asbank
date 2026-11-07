@extends('layouts.admin')

@section('header-title', 'Contrato ' . $loan->contract_number)

@section('content')
<div class="space-y-6">

    <!-- Top Navigation & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <a href="{{ route('admin.loans.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar para lista de empréstimos
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.loans.contract-print', $loan) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors flex items-center gap-1.5 shadow">
                <i data-lucide="printer" class="w-4 h-4"></i> Imprimir Contrato
            </a>
            <a href="{{ route('admin.loans.edit', $loan) }}" class="px-4 py-2 rounded-xl bg-sky-600/20 text-sky-300 border border-sky-500/30 hover:bg-sky-600/30 font-semibold text-xs transition-colors flex items-center gap-1.5">
                <i data-lucide="pencil" class="w-4 h-4"></i> Editar Contrato
            </a>
        </div>
    </div>

    <!-- Loan Overview Banner -->
    @php 
        $statusBadge = $loan->statusBadge();
        $disbBadge = $loan->disbursementBadge();
    @endphp
    <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-600 to-amber-500 flex items-center justify-center font-black text-white text-lg shadow-lg">
                <i data-lucide="file-text" class="w-7 h-7"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black text-white font-mono">{{ $loan->contract_number }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $statusBadge['class'] }}">
                        {{ $statusBadge['label'] }}
                    </span>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 mt-1">
                    <span>Mutuário: <a href="{{ route('admin.clients.show', $loan->client) }}" class="font-bold text-sky-400 hover:underline">{{ $loan->client->name }}</a></span>
                    <span>•</span>
                    <span class="font-mono">CPF: {{ $loan->client->formattedDocument() }}</span>
                    <span>•</span>
                    <span>Emissão: {{ $loan->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <div class="p-4 rounded-xl bg-[#070D18] border border-[#162540] text-right">
                <span class="text-xs text-slate-400">Valor Solicitado</span>
                <div class="text-xl font-black text-white">R$ {{ number_format($loan->amount, 2, ',', '.') }}</div>
            </div>
            <div class="p-4 rounded-xl bg-[#070D18] border border-[#162540] text-right">
                <span class="text-xs text-slate-400">Total c/ Juros ({{ $loan->interest_rate }}% a.m.)</span>
                <div class="text-xl font-black text-sky-400">R$ {{ number_format($loan->total_amount, 2, ',', '.') }}</div>
            </div>
            <div class="p-4 rounded-xl bg-[#070D18] border border-amber-500/20 text-right">
                <span class="text-xs text-slate-400">Score de Crédito</span>
                <div class="text-xl font-black text-amber-400 flex items-center justify-end gap-1">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i> {{ $loan->credit_score }}
                </div>
            </div>
        </div>
    </div>

    <!-- DISBURSEMENT BOX: Envio do PIX (Cash-Out MisticPay) -->
    <div class="p-6 rounded-2xl bg-[#0A1324] border {{ $loan->disbursement_status === 'enviado' ? 'border-emerald-500/30' : 'border-amber-500/30' }} shadow-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="p-3.5 rounded-2xl {{ $loan->disbursement_status === 'enviado' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                    <i data-lucide="zap" class="w-7 h-7"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-white">Desembolso Financeiro via PIX (MisticPay Cash-Out)</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $disbBadge['class'] }}">
                            {{ $disbBadge['label'] }}
                        </span>
                    </div>

                    @if($loan->disbursement_status === 'enviado')
                        <p class="text-xs text-emerald-400/90 mt-1">
                            PIX de <strong>R$ {{ number_format($loan->amount, 2, ',', '.') }}</strong> enviado para a chave <strong class="font-mono text-white">{{ $loan->pix_key }}</strong> em {{ $loan->disbursed_at?->format('d/m/Y H:i') }}.
                        </p>
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-slate-400 mt-2 font-mono">
                            <span>ID MisticPay: <span class="text-slate-200">{{ $loan->disbursement_transaction_id }}</span></span>
                            <span>•</span>
                            <span>End-to-End: <span class="text-slate-200">{{ $loan->disbursement_e2e }}</span></span>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 mt-1">
                            Os fundos de <strong>R$ {{ number_format($loan->amount, 2, ',', '.') }}</strong> serão transferidos instantaneamente para a conta do cliente via API MisticPay.
                        </p>
                        <div class="flex items-center gap-2 mt-2 text-xs text-amber-300 font-mono">
                            <span>Destino: {{ strtoupper($loan->pix_key_type ?: $loan->client->pix_key_type) }} <strong>{{ $loan->pix_key ?: $loan->client->pix_key }}</strong> ({{ $loan->client->bank_name ?: 'Conta Titular' }})</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Button for PIX Cash-Out -->
            <div>
                @if($loan->disbursement_status === 'enviado')
                    <div class="px-4 py-2 rounded-xl bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 text-xs font-bold flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                        <span>Transferência Liquidada</span>
                    </div>
                @else
                    <form method="POST" action="{{ route('admin.loans.send-pix', $loan) }}" onsubmit='return confirm("Confirma o envio do PIX de R$ {{ number_format($loan->amount, 2, ',', '.') }} para {{ $loan->client->name }}? O valor sairá do saldo MisticPay.");'>
                        @csrf
                        <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-sm uppercase tracking-wider shadow-lg shadow-amber-900/30 transition-all flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Enviar PIX Agora</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Installments Schedule Table (Cronograma de Parcelas & PIX de Cobrança) -->
    <div class="rounded-2xl bg-[#0A1324] border border-[#162540] overflow-hidden shadow-xl">
        <div class="p-6 border-b border-[#162540] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="calendar" class="w-5 h-5 text-sky-400"></i> Cronograma de Parcelas & Cobrança PIX
                </h2>
                <p class="text-xs text-slate-400">Gere QR Codes PIX dinâmicos (MisticPay Cash-In) para recebimento das parcelas.</p>
            </div>

            <div class="flex items-center gap-4 text-xs font-semibold">
                <span class="text-emerald-400 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $loan->paidInstallmentsCount() }} Pagas
                </span>
                <span class="text-amber-400 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> {{ $loan->pendingInstallmentsCount() }} Abertas
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-[#070D18] text-xs font-semibold uppercase text-slate-400 tracking-wider border-b border-[#162540]">
                    <tr>
                        <th class="px-6 py-4">Parcela</th>
                        <th class="px-6 py-4">Vencimento</th>
                        <th class="px-6 py-4">Valor</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">PIX de Cobrança</th>
                        <th class="px-6 py-4 text-right">Ações</th>
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
                                <div class="text-[10px] text-slate-400 mt-1">Pago em {{ $inst->paid_at->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($inst->status === 'pago')
                                <span class="text-xs text-emerald-400 flex items-center gap-1 font-medium">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Quitado via PIX
                                </span>
                            @elseif($inst->pix_qr_code)
                                <button type="button" onclick="openPixModal('{{ $inst->installment_number }}', '{{ number_format($inst->amount, 2, ',', '.') }}', '{{ $inst->pix_qr_code }}', '{{ $inst->pix_qr_code_base64 }}')" 
                                        class="px-3 py-1.5 rounded-lg bg-sky-500/20 text-sky-300 border border-sky-500/30 hover:bg-sky-500/30 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-sky-400"></i>
                                    <span>Ver QR Code PIX</span>
                                </button>
                            @else
                                <form method="POST" action="{{ route('admin.loans.generate-installment-pix', $inst) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 hover:bg-amber-500/30 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                                        <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-400"></i>
                                        <span>Gerar PIX MisticPay</span>
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($inst->status !== 'pago')
                                    <form method="POST" action="{{ route('admin.loans.mark-installment-paid', $inst) }}" onsubmit="return confirm('Confirmar baixa manual desta parcela?')">
                                        @csrf
                                        <button type="submit" title="Marcar como Pago Manualmente" class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 text-xs font-semibold transition-colors flex items-center gap-1">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Baixa
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Contract & Analysis Terms Preview -->
    <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] shadow-xl">
        <h2 class="text-base font-bold text-white flex items-center gap-2 mb-4 pb-2 border-b border-[#162540]">
            <i data-lucide="file-check" class="w-5 h-5 text-emerald-400"></i> Termos e Cláusulas do Contrato Digital ASBANK
        </h2>
        <div class="p-5 rounded-xl bg-[#070D18] border border-[#162540] max-h-72 overflow-y-auto">
            {!! $loan->contract_terms !!}
        </div>
    </div>

</div>

<!-- PIX QR Code Modal -->
<div id="pixModal" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 hidden">
    <div class="bg-[#0A1324] border border-[#162540] rounded-2xl max-w-md w-full p-6 text-center shadow-2xl relative animate-fadeIn">
        <button onclick="closePixModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        <div class="inline-flex p-3 rounded-2xl bg-amber-500/10 text-amber-400 mb-3">
            <i data-lucide="qr-code" class="w-8 h-8"></i>
        </div>

        <h3 class="text-lg font-bold text-white">PIX de Cobrança ASBANK</h3>
        <p class="text-xs text-slate-400 mt-1" id="modalParcelText">Parcela #1 - R$ 0,00</p>

        <!-- QR Code Image -->
        <div class="my-6 p-4 rounded-2xl bg-white mx-auto inline-block shadow-lg">
            <img id="modalQrImage" src="" alt="QR Code PIX" class="w-56 h-56 object-contain">
        </div>

        <!-- Copia e Cola -->
        <div class="space-y-3">
            <label class="block text-xs font-semibold text-slate-400 text-left">Código PIX Copia e Cola:</label>
            <div class="relative">
                <textarea id="modalPixCode" readonly rows="2" class="w-full px-3 py-2 text-xs font-mono bg-[#070D18] border border-[#162540] rounded-xl text-slate-200 resize-none"></textarea>
            </div>
            <button type="button" onclick="copyPixCode()" class="w-full py-3 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-sky-900/30 transition-all">
                <i data-lucide="copy" class="w-4 h-4"></i> Copiar Código PIX
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openPixModal(parcelNumber, amount, pixCode, qrImage) {
        document.getElementById('modalParcelText').innerText = `Parcela #${parcelNumber} • R$ ${amount}`;
        document.getElementById('modalPixCode').value = pixCode;
        document.getElementById('modalQrImage').src = qrImage || `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(pixCode)}`;
        document.getElementById('pixModal').classList.remove('hidden');
    }

    function closePixModal() {
        document.getElementById('pixModal').classList.add('hidden');
    }

    function copyPixCode() {
        const code = document.getElementById('modalPixCode').value;
        copyToClipboard(code, 'Código PIX Copia e Cola copiado com sucesso!');
    }
</script>
@endpush
