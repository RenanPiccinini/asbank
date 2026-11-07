@extends('layouts.client')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Back link -->
    <div>
        <a href="{{ route('client.loans.show', $installment->loan) }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar para detalhes do contrato
        </a>
    </div>

    <!-- Main Payment Card -->
    <div id="paymentBox" class="p-8 rounded-3xl bg-[#0A1324] border border-amber-500/30 text-center space-y-6 shadow-2xl relative overflow-hidden">
        
        <!-- Header -->
        <div>
            <div class="inline-flex p-3 rounded-2xl bg-amber-500/10 text-amber-400 mb-2 border border-amber-500/20">
                <i data-lucide="qr-code" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-black text-white">Pagamento via PIX Instantâneo</h1>
            <p class="text-xs text-slate-400 mt-1">
                Parcela <strong>#{{ $installment->installment_number }}</strong> de {{ $installment->loan->installments_count }} • Contrato <strong>{{ $installment->loan->contract_number }}</strong>
            </p>
        </div>

        <!-- Value Highlight -->
        <div class="p-4 rounded-2xl bg-[#070D18] border border-[#162540] inline-block px-8">
            <span class="text-xs text-slate-400 block mb-0.5">Valor da Parcela</span>
            <span class="text-3xl font-black text-amber-400 tracking-tight">
                R$ {{ number_format($installment->amount, 2, ',', '.') }}
            </span>
            <div class="text-[11px] text-slate-400 mt-1 font-mono">
                Vencimento: {{ $installment->due_date->format('d/m/Y') }}
            </div>
        </div>

        <!-- Live Status Pill -->
        <div class="flex items-center justify-center gap-2 text-xs font-semibold text-slate-400">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
            </span>
            <span>Aguardando pagamento no banco... (baixa automática)</span>
        </div>

        <!-- QR Code Image -->
        <div class="p-4 rounded-2xl bg-white mx-auto inline-block shadow-2xl border-4 border-amber-400/20">
            @if($installment->pix_qr_code_base64)
                <img src="{{ $installment->pix_qr_code_base64 }}" alt="QR Code PIX" class="w-60 h-60 object-contain mx-auto">
            @else
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($installment->pix_qr_code) }}" alt="QR Code PIX" class="w-60 h-60 object-contain mx-auto">
            @endif
        </div>

        <!-- Copia e Cola -->
        <div class="space-y-3 text-left max-w-lg mx-auto">
            <label class="block text-xs font-bold text-slate-300">Código PIX Copia e Cola:</label>
            <div class="relative">
                <textarea id="pixCodeInput" readonly rows="2" class="w-full px-3 py-2 text-xs font-mono bg-[#070D18] border border-[#162540] rounded-xl text-slate-300 resize-none">{{ $installment->pix_qr_code }}</textarea>
            </div>
            <button type="button" onclick="copyToClipboard(document.getElementById('pixCodeInput').value, 'Código PIX Copia e Cola copiado com sucesso!')" 
                    class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-amber-900/30 transition-all">
                <i data-lucide="copy" class="w-4 h-4"></i> Copiar Código PIX
            </button>
        </div>

        <!-- How to Pay Step-by-Step -->
        <div class="p-5 rounded-2xl bg-[#070D18] border border-[#162540] text-left space-y-3 max-w-lg mx-auto text-xs text-slate-300">
            <span class="font-bold text-white block pb-1 border-b border-[#162540]">Como pagar em 3 passos:</span>
            <div class="flex items-start gap-3">
                <span class="w-5 h-5 rounded-full bg-sky-500/20 text-sky-400 font-bold flex items-center justify-center shrink-0 text-[10px]">1</span>
                <span>Abra o aplicativo do seu banco (qualquer instituição financeira).</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-5 h-5 rounded-full bg-sky-500/20 text-sky-400 font-bold flex items-center justify-center shrink-0 text-[10px]">2</span>
                <span>Escolha a opção <strong>PIX</strong> e selecione <strong>Ler QR Code</strong> ou <strong>PIX Copia e Cola</strong>.</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-5 h-5 rounded-full bg-sky-500/20 text-sky-400 font-bold flex items-center justify-center shrink-0 text-[10px]">3</span>
                <span>Confirme o valor e finalize. A compensação é <strong>imediata</strong> via gateway MisticPay!</span>
            </div>
        </div>

    </div>

    <!-- Success Container (shown via live polling when paid) -->
    <div id="successBox" class="p-8 rounded-3xl bg-[#0A1324] border border-emerald-500/40 text-center space-y-6 shadow-2xl hidden">
        <div class="w-20 h-20 rounded-full bg-emerald-500/20 border-2 border-emerald-500 text-emerald-400 mx-auto flex items-center justify-center shadow-lg">
            <i data-lucide="check" class="w-10 h-10"></i>
        </div>

        <div>
            <h2 class="text-2xl font-black text-white">Pagamento Confirmado!</h2>
            <p class="text-xs text-emerald-400 mt-1 font-semibold">
                Sua parcela #{{ $installment->installment_number }} foi devidamente liquidada via PIX.
            </p>
        </div>

        <div class="p-4 rounded-2xl bg-[#070D18] border border-[#162540] max-w-md mx-auto text-xs text-slate-300 space-y-2">
            <div class="flex justify-between">
                <span class="text-slate-400">Valor Pago:</span>
                <span class="font-bold text-white">R$ {{ number_format($installment->amount, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Data/Hora:</span>
                <span id="paidAtTime" class="font-mono text-slate-200">Agora</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Identificador E2E:</span>
                <span id="paidE2eCode" class="font-mono text-slate-400 truncate max-w-[200px]">{{ $installment->pix_e2e ?: 'E2E-MISTICPAY-PIX' }}</span>
            </div>
        </div>

        <div>
            <a href="{{ route('client.loans.show', $installment->loan) }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-emerald-900/30 transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar ao Meu Contrato
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const checkStatusUrl = "{{ route('client.loans.installment-status', $installment) }}";
    let pollInterval = setInterval(() => {
        fetch(checkStatusUrl)
            .then(res => res.json())
            .then(data => {
                if (data.is_paid) {
                    clearInterval(pollInterval);
                    document.getElementById('paymentBox').classList.add('hidden');
                    document.getElementById('successBox').classList.remove('hidden');
                    if (data.paid_at) {
                        document.getElementById('paidAtTime').innerText = data.paid_at;
                    }
                    if (data.e2e) {
                        document.getElementById('paidE2eCode').innerText = data.e2e;
                    }
                    showToast('🎉 Pagamento PIX confirmado com sucesso!');
                    lucide.createIcons();
                }
            })
            .catch(err => console.log('Checking status...'));
    }, 3000);
</script>
@endpush
