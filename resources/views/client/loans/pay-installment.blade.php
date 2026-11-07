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
    <div class="p-8 rounded-3xl bg-[#0A1324] border border-amber-500/30 text-center space-y-6 shadow-2xl relative overflow-hidden">
        
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
            <button type="button" onclick="copyToClipboard(document.getElementById('pixCodeInput').value, 'Código PIX Copia e Cola copiado!')" 
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

</div>
@endsection

