<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASBANK') }} - Área do Cliente</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#070D18] text-slate-100 min-h-screen flex flex-col">

    <!-- Top Navigation Bar for Client Portal -->
    <header class="bg-[#0A1324] border-b border-[#162540] sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand Logo -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK" class="h-10 w-auto rounded-lg shadow-md border border-sky-500/20">
                    </a>

                    <!-- Navigation Links -->
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('client.dashboard') }}" 
                           class="px-4 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('client.dashboard') ? 'bg-sky-600/20 text-sky-400 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            Painel Principal
                        </a>
                        <a href="{{ route('client.loans.index') }}" 
                           class="px-4 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('client.loans.*') ? 'bg-sky-600/20 text-sky-400 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            Meus Empréstimos & Contratos
                        </a>
                        <a href="{{ route('client.statement') }}" 
                           class="px-4 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('client.statement') ? 'bg-sky-600/20 text-sky-400 border border-sky-500/30 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                            Meu Extrato PIX
                        </a>
                    </nav>
                </div>

                <!-- User & Actions -->
                <div class="flex items-center gap-4">
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-semibold hover:bg-amber-500/30 transition-colors">
                            <i data-lucide="shield" class="w-3.5 h-3.5"></i> Voltar ao Painel Admin
                        </a>
                    @endif

                    <div class="flex items-center gap-3 pl-3 border-l border-slate-800">
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-sky-400">Cliente ASBANK</div>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-sky-600 to-amber-500 flex items-center justify-center font-bold text-white text-sm shadow-md">
                            {{ substr(auth()->user()->name ?? 'C', 0, 2) }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Sair" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile Submenu -->
            <div class="flex md:hidden items-center justify-between py-2.5 border-t border-[#162540] text-xs font-medium text-slate-300">
                <a href="{{ route('client.dashboard') }}" class="px-2 py-1 {{ request()->routeIs('client.dashboard') ? 'text-sky-400 font-bold' : '' }}">Início</a>
                <a href="{{ route('client.loans.index') }}" class="px-2 py-1 {{ request()->routeIs('client.loans.*') ? 'text-sky-400 font-bold' : '' }}">Empréstimos</a>
                <a href="{{ route('client.statement') }}" class="px-2 py-1 {{ request()->routeIs('client.statement') ? 'text-sky-400 font-bold' : '' }}">Extrato</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="px-2 py-1 text-amber-400">Admin</a>
                @endif
            </div>
        </div>
    </header>

    <!-- Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm mb-6">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5"></i>
                <div class="flex-1 font-medium">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-start gap-3 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm mb-6">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5"></i>
                <div class="flex-1 font-medium">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-[#0A1324] border-t border-[#162540] py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span>© {{ date('Y') }} ASBANK • Confiança que cresce. Todos os direitos reservados.</span>
            <span class="flex items-center gap-2 text-slate-400">
                <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-400"></i> Ambiente Criptografado & Seguro PIX
            </span>
        </div>
    </footer>

    <script>
        lucide.createIcons();
        function copyToClipboard(text, message = 'Código PIX copiado com sucesso!') {
            navigator.clipboard.writeText(text).then(() => {
                alert(message);
            }).catch(err => {
                console.error('Falha ao copiar:', err);
            });
        }
    </script>
    @stack('scripts')
</body>
</html>

