<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitação Enviada • ASBANK</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            background-color: #050A14;
            color: #F8FAFC;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            margin: 0;
        }
        .site-container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .glass-card {
            background: rgba(10, 19, 36, 0.92);
            border: 1px solid rgba(56, 189, 248, 0.22);
            border-radius: 1.5rem;
            padding: 1.5rem;
            max-width: 720px;
            width: 100%;
            margin: 0 auto;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);
        }
        @media (min-width: 640px) {
            .glass-card { padding: 2.5rem; }
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        @media (min-width: 640px) {
            .info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sm\:h-10 { height: 2.5rem; }
            .sm\:h-20 { height: 5rem; }
            .sm\:p-5 { padding: 1.25rem; }
            .sm\:py-16 { padding-top: 4rem; padding-bottom: 4rem; }
        }
        .bg-\[\#040812\] { background-color: #040812; }
        .bg-\[\#050A14\]\/90 { background-color: rgba(5, 10, 20, 0.9); }
        .bg-amber-500\/15 { background-color: rgba(245, 158, 11, 0.15); }
        .border-\[\#1E3252\] { border-color: #1E3252; }
        .border-sky-500\/15 { border-color: rgba(14, 165, 233, 0.15); }
        .border-sky-500\/25 { border-color: rgba(14, 165, 233, 0.25); }
        .py-10 { padding-top: 2.5rem; padding-bottom: 2.5rem; }
        .to-blue-600 { --tw-gradient-to: #2563EB; }
        .hover\:from-sky-400:hover { --tw-gradient-from: #38BDF8; --tw-gradient-to: rgba(56, 189, 248, 0); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .hover\:to-blue-500:hover { --tw-gradient-to: #3B82F6; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    <header class="border-b border-sky-500/15 bg-[#050A14]/90">
        <div class="site-container h-16 sm:h-20 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK" class="h-9 sm:h-10 w-auto rounded-xl border border-sky-500/30">
            </a>
            <a href="{{ route('home') }}" class="px-4 py-2 rounded-xl bg-[#0A1324] border border-[#1E3252] text-xs font-semibold text-slate-300 hover:text-white transition">
                Voltar ao Início
            </a>
        </div>
    </header>

    <main class="flex-1 py-10 sm:py-16 flex items-center">
        <div class="site-container">
            <div class="glass-card space-y-6">
                <div class="flex flex-col items-center text-center space-y-3">
                    <div class="w-16 h-16 rounded-2xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center">
                        <i data-lucide="clock" class="w-8 h-8 text-amber-400"></i>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-500/15 text-amber-300 border border-amber-500/30">
                        Proposta Em Análise
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-white">
                        Solicitação enviada com sucesso!
                    </h1>
                    <p class="text-sm text-slate-300 max-w-lg leading-relaxed">
                        Olá, <strong>{{ $loan->client->name }}</strong>! Recebemos seus dados completos e sua solicitação de crédito já está com a equipe de análise do ASBANK.
                    </p>
                </div>

                <div class="info-grid p-4 sm:p-5 rounded-2xl bg-[#070D18] border border-[#162540]">
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">Número da Proposta</span>
                        <div class="text-base font-black text-white font-mono mt-0.5">{{ $loan->contract_number }}</div>
                    </div>
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">Status do Cadastro</span>
                        <div class="text-sm font-bold text-amber-400 mt-0.5">Aguardando Aprovação (Acesso Bloqueado)</div>
                    </div>
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">Valor Solicitado</span>
                        <div class="text-base font-bold text-sky-400 mt-0.5">R$ {{ number_format($loan->amount, 2, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">Parcelamento Simulado</span>
                        <div class="text-base font-bold text-white mt-0.5">{{ $loan->installments_count }}x de R$ {{ number_format($loan->installment_value, 2, ',', '.') }}</div>
                    </div>
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">WhatsApp de Contato</span>
                        <div class="text-sm font-mono text-emerald-400 mt-0.5">{{ $loan->client->formattedPhone() }}</div>
                    </div>
                    <div>
                        <span class="text-[11px] uppercase tracking-wider text-slate-500 font-bold">Chave PIX Informada</span>
                        <div class="text-sm font-mono text-slate-200 mt-0.5 break-all">{{ $loan->pix_key }}</div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-sky-500/10 border border-sky-500/25 flex items-start gap-3 text-xs text-slate-300 leading-relaxed">
                    <i data-lucide="lock" class="w-5 h-5 text-sky-400 shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="text-white block mb-1">Atenção sobre seu acesso ao Portal do Cliente:</strong>
                        Por segurança, sua conta com e-mail <strong>{{ $loan->client->email }}</strong> permanece temporariamente bloqueada enquanto nossa equipe analisa sua proposta. Assim que aprovada, liberaremos o seu login e enviaremos a confirmação diretamente no seu <strong>WhatsApp</strong>.
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                    <a href="{{ route('home') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white font-bold text-xs uppercase tracking-wider text-center transition">
                        Voltar para a Página Inicial
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-[#040812] py-8 text-center text-xs text-slate-500" style="border-top: 1px solid rgba(255,255,255,0.08);">
        <div class="site-container flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK" class="h-8 w-auto rounded-lg border border-sky-500/20">
                <span>© {{ date('Y') }} ASBANK • Confiança que cresce. Todos os direitos reservados.</span>
            </div>

            <div class="text-slate-400 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Operações PIX 24/7 • Tecnologia Financeira</span>
            </div>

            <div class="flex items-center gap-2.5 text-slate-400">
                <span class="text-xs font-medium">Desenvolvido por</span>
                <a href="https://piccininidigitalsolutions.com.br/" target="_blank" rel="noopener noreferrer"
                   title="Piccinini Digital Solutions"
                   class="inline-flex items-center bg-white rounded-xl p-1.5 shadow-md border border-sky-500/30 hover:border-sky-400 transition-all">
                    <img src="{{ asset('images/pds-logo.png') }}" alt="Piccinini Digital Solutions" class="h-10 w-auto object-contain rounded-lg">
                </a>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
