<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ASBANK') }} - Confiança que cresce</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#070D18] text-slate-100 min-h-screen flex flex-col selection:bg-amber-500 selection:text-slate-950">

    <!-- Top Navigation -->
    <header class="border-b border-[#162540] bg-[#0A1324]/80 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK - Confiança que cresce" class="h-11 w-auto rounded-xl shadow-lg border border-sky-500/20">
            </a>

            <div class="flex items-center gap-3">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-amber-900/30 transition-all flex items-center gap-1.5">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            <span>Gerenciador Admin</span>
                        </a>
                    @else
                        <a href="{{ route('client.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-sky-900/30 transition-all flex items-center gap-1.5">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Meu Painel do Cliente</span>
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-sky-900/30 transition-all flex items-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Acessar Plataforma</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Column -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#0F1D38] border border-amber-500/30 text-amber-300 text-xs font-semibold">
                        <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
                        <span>Integrado 100% com Gateway MisticPay PIX</span>
                    </div>

                    <h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight leading-tight">
                        ASBANK <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-blue-400 to-amber-400">
                            Confiança que cresce.
                        </span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl">
                        Plataforma inteligente de concessão de empréstimos mútuos com geração automática de contratos digitais, análise de score de crédito, envio instantâneo de PIX e liquidação automática via Webhooks MisticPay.
                    </p>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        @auth
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('client.dashboard') }}" class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-sky-900/30 transition-all inline-flex items-center gap-2">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                <span>Ir para o Painel</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-sky-900/30 transition-all inline-flex items-center gap-2">
                                <i data-lucide="log-in" class="w-4 h-4"></i>
                                <span>Acessar Minha Conta</span>
                            </a>
                        @endauth
                        <div class="flex items-center gap-4 text-xs text-slate-400">
                            <span class="flex items-center gap-1.5"><i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i> 100% Digital</span>
                            <span class="flex items-center gap-1.5"><i data-lucide="zap" class="w-4 h-4 text-amber-400"></i> PIX Instantâneo</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Simulator -->
                <div class="lg:col-span-5">
                    <div class="p-8 rounded-3xl bg-gradient-to-b from-[#0A1324] to-[#070D18] border border-[#162540] shadow-2xl space-y-6 relative">
                        <div class="flex items-center justify-between pb-4 border-b border-[#162540]">
                            <div class="flex items-center gap-2">
                                <i data-lucide="calculator" class="w-5 h-5 text-amber-400"></i>
                                <h2 class="text-base font-bold text-white">Simulador de Crédito</h2>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-sky-500/20 text-sky-300">2.50% a.m.</span>
                        </div>

                        <!-- Simulator Inputs -->
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between text-xs text-slate-300 mb-1">
                                    <span>Valor Desejado</span>
                                    <span class="font-bold text-white" id="rangeAmountLabel">R$ 5.000,00</span>
                                </div>
                                <input type="range" id="rangeAmount" min="500" max="50000" step="500" value="5000" 
                                       oninput="updateSimRange()"
                                       class="w-full accent-amber-500 cursor-pointer">
                            </div>

                            <div>
                                <div class="flex justify-between text-xs text-slate-300 mb-1">
                                    <span>Prazo de Pagamento</span>
                                    <span class="font-bold text-white" id="rangeMonthsLabel">12 meses</span>
                                </div>
                                <input type="range" id="rangeMonths" min="1" max="36" step="1" value="12" 
                                       oninput="updateSimRange()"
                                       class="w-full accent-sky-500 cursor-pointer">
                            </div>
                        </div>

                        <!-- Simulator Result Box -->
                        <div class="p-5 rounded-2xl bg-[#0F1D38] border border-sky-500/30 text-center space-y-2">
                            <span class="text-xs text-slate-400">Parcela Mensal Estimada</span>
                            <div class="text-3xl font-black text-amber-400 tracking-tight" id="resParcel">
                                12x de R$ 541,67
                            </div>
                            <div class="text-xs text-slate-300" id="resTotal">
                                Total a pagar: R$ 6.500,00
                            </div>
                        </div>

                        <a href="{{ route('login') }}" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg shadow-amber-900/30 transition-all">
                            <i data-lucide="check" class="w-4 h-4"></i> Contratar Agora via PIX
                        </a>
                    </div>
                </div>

            </div>

            <!-- Features 3-Column Strip -->
            <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Desembolso PIX (Cash-Out)</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Envie os valores dos empréstimos aprovados diretamente para a chave PIX do cliente com 1 clique pela API oficial MisticPay.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="qr-code" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Cobrança PIX (Cash-In)</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Geração automática de QR Code dinâmico e código Copia e Cola para cada parcela, com liquidação automática via webhook.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-3">
                    <div class="w-12 h-12 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center">
                        <i data-lucide="file-check" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Contratos & Análise de Score</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Emissão instantânea de instrumentos contratuais com parecer de crédito, cláusulas bancárias e portal exclusivo para o cliente.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-[#0A1324] border-t border-[#162540] py-8 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK" class="h-8 w-auto rounded-lg">
                <span>© {{ date('Y') }} ASBANK • Confiança que cresce. Todos os direitos reservados.</span>
            </div>
            <div class="text-slate-400">
                Integração MisticPay Gateway V1 • Operações PIX 24/7
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();

        function updateSimRange() {
            const amount = parseFloat(document.getElementById('rangeAmount').value);
            const months = parseInt(document.getElementById('rangeMonths').value);
            const rate = 0.025; // 2.5% a.m.

            const total = amount + (amount * rate * months);
            const parcel = total / months;

            document.getElementById('rangeAmountLabel').innerText = 'R$ ' + amount.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            document.getElementById('rangeMonthsLabel').innerText = months + (months === 1 ? ' mês' : ' meses');
            document.getElementById('resParcel').innerText = months + 'x de R$ ' + parcel.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('resTotal').innerText = 'Total a pagar: R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        document.addEventListener('DOMContentLoaded', updateSimRange);
    </script>
</body>
</html>
