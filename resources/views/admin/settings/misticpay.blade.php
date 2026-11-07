@extends('layouts.admin')

@section('header-title', 'Configurações Gateway MisticPay')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Top Status Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-[#0F1D38] via-[#0A1324] to-[#0F1D38] border border-sky-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="p-3.5 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30">
                <i data-lucide="zap" class="w-8 h-8"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold text-white">Integração MisticPay PIX</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isConfigured ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                        {{ $isConfigured ? 'Chaves Preenchidas' : 'Aguardando Chaves' }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isMock ? 'bg-amber-500/20 text-amber-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                        {{ $isMock ? 'Modo Simulação Ativo' : 'Ambiente Real' }}
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Cash-In (Recebimento de parcelas via QR Code) e Cash-Out (Desembolso de empréstimo para chaves PIX).
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.misticpay.test') }}">
            @csrf
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-sky-400 hover:text-white font-semibold text-xs border border-sky-500/30 transition-all flex items-center gap-2 shadow">
                <i data-lucide="activity" class="w-4 h-4"></i> Testar Conexão API
            </button>
        </form>
    </div>

    <!-- Credentials & Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Column -->
        <div class="lg:col-span-2 space-y-6">
            <form method="POST" action="{{ route('admin.settings.misticpay.update') }}" class="p-8 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-6 shadow-xl">
                @csrf

                <div>
                    <h2 class="text-base font-bold text-white flex items-center gap-2 mb-1">
                        <i data-lucide="key" class="w-5 h-5 text-amber-400"></i> Credenciais de Acesso à API
                    </h2>
                    <p class="text-xs text-slate-400">Insira suas chaves fornecidas no painel da MisticPay.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">URL Base da API</label>
                    <input type="url" name="misticpay_base_url" value="{{ old('misticpay_base_url', $baseUrl) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white font-mono focus:border-sky-500">
                    <p class="text-[11px] text-slate-500 mt-1">Padrão de produção: https://api.misticpay.com</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Chave Pública (Public Key / Client ID) *</label>
                    <input type="text" name="misticpay_public_key" value="{{ old('misticpay_public_key', $publicKey) }}" 
                           placeholder="pk_live_... ou seu Client ID"
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white font-mono focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Chave Secreta (Secret Key / Client Secret) *</label>
                    <div class="relative">
                        <input type="password" id="misticpay_secret_key" name="misticpay_secret_key" value="{{ old('misticpay_secret_key', $secretKey) }}" 
                               placeholder="sk_live_... ou seu Client Secret"
                               class="w-full pl-4 pr-12 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white font-mono focus:border-sky-500">
                        <button type="button" onclick="toggleSecretVisibility()" 
                                class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-white transition-colors focus:outline-none"
                                title="Mostrar/Ocultar chave secreta">
                            <svg id="eyeIconOpen" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eyeIconClosed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-[#070D18] border border-[#162540]">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="misticpay_mock" value="1" {{ $isMock ? 'checked' : '' }} class="mt-1 w-4 h-4 rounded text-sky-600 bg-slate-900 border-slate-700">
                        <div>
                            <span class="text-xs font-bold text-white block">Ativar Modo Simulação (Sandbox / Mock)</span>
                            <span class="text-[11px] text-slate-400">
                                Permite simular a geração de QR Codes e envios de PIX com sucesso sem debitar dinheiro real da sua conta MisticPay. Desmarque para operar em ambiente de produção real.
                            </span>
                        </div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#162540]">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-sm shadow-lg shadow-sky-900/30 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Salvar Configurações
                    </button>
                </div>
            </form>

            <!-- Webhook Endpoint Box -->
            <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-4 shadow-xl">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-sm">
                    <i data-lucide="webhook" class="w-5 h-5"></i> URL do Webhook para Notificações Automáticas
                </div>
                <p class="text-xs text-slate-400">
                    Cadastre este endereço no painel da MisticPay na aba <strong>Webhooks</strong>. Toda vez que um cliente pagar um PIX ou um envio for liquidado, nosso sistema atualizará o status em tempo real.
                </p>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ $webhookUrl }}" id="webhook_url_input" 
                           class="flex-1 px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-xs font-mono text-emerald-400">
                    <button type="button" onclick="copyToClipboard('{{ $webhookUrl }}', 'URL do Webhook copiada!')" 
                            class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs flex items-center gap-1.5 transition-colors whitespace-nowrap">
                        <i data-lucide="copy" class="w-4 h-4"></i> Copiar URL
                    </button>
                </div>
            </div>
        </div>

        <!-- Documentation & Guide Column -->
        <div class="space-y-6">
            <div class="p-6 rounded-2xl bg-[#0A1324] border border-amber-500/20 space-y-4 shadow-xl">
                <div class="flex items-center gap-2 text-amber-400 font-bold text-sm uppercase tracking-wider pb-2 border-b border-[#162540]">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Onde e Como Pegar as Chaves
                </div>

                <div class="space-y-3.5 text-xs text-slate-300 leading-relaxed">
                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center shrink-0 text-[11px]">1</span>
                        <p>Acesse o painel oficial da MisticPay em <a href="https://painel.misticpay.com" target="_blank" class="text-sky-400 underline font-semibold">painel.misticpay.com</a>.</p>
                    </div>

                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center shrink-0 text-[11px]">2</span>
                        <p>No menu lateral esquerdo, clique em <strong>API</strong> e em seguida em <strong>Chaves de Acesso</strong>.</p>
                    </div>

                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center shrink-0 text-[11px]">3</span>
                        <p>Clique no botão <strong>Criar Chave de Acesso</strong>.</p>
                    </div>

                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center shrink-0 text-[11px]">4</span>
                        <div>
                            <p class="font-bold text-white mb-1">Selecione os escopos necessários:</p>
                            <ul class="space-y-1 text-slate-400 text-[11px]">
                                <li>• <strong class="text-emerald-400">TRANSACTION_CREATE</strong> (Gerar PIX Cash-In)</li>
                                <li>• <strong class="text-amber-400">WITHDRAW_CREATE</strong> (Enviar PIX Cash-Out)</li>
                                <li>• <strong class="text-sky-400">TRANSACTION_READ</strong> (Consultar status)</li>
                                <li>• <strong class="text-sky-400">BALANCE_READ</strong> (Consultar saldo)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center shrink-0 text-[11px]">5</span>
                        <p>Copie a <strong>Chave Pública</strong> (<code class="text-sky-300">pk_...</code>) e a <strong>Chave Secreta</strong> (<code class="text-sky-300">sk_...</code>) e cole no formulário ao lado.</p>
                    </div>

                    <div class="flex gap-2.5">
                        <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center shrink-0 text-[11px]">6</span>
                        <p>Em <strong>API → IPs Autorizados</strong>, certifique-se de adicionar o IP do servidor em que este sistema estiver hospedado caso ative saques em produção.</p>
                    </div>
                </div>
            </div>

            <!-- Architecture Box -->
            <div class="p-6 rounded-2xl bg-[#070D18] border border-[#162540] text-xs text-slate-400 space-y-2">
                <span class="font-bold text-white block">Arquitetura de Segurança ASBANK:</span>
                <p>• As requisições trafegam com cabeçalho <code>Authorization: Basic base64(pk:sk)</code> criptografado.</p>
                <p>• Os valores são tratados em reais com casas decimais conforme especificação oficial v1 da MisticPay.</p>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    function toggleSecretVisibility() {
        const input = document.getElementById('misticpay_secret_key');
        const eyeOpen = document.getElementById('eyeIconOpen');
        const eyeClosed = document.getElementById('eyeIconClosed');

        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        } else {
            input.type = 'password';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        }
    }
</script>
@endpush

