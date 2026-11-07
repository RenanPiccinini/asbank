<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASBANK') }} - Gerenciador Administrativo</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#070D18] text-slate-100 min-h-screen flex">

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black/60 z-40 lg:hidden hidden" onclick="toggleSidebar()"></div>

    <!-- LEFT SIDEBAR (Menu do Gerenciador na Esquerda) -->
    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-[#0A1324] border-r border-[#162540] flex flex-col transition-transform duration-300 -translate-x-full lg:translate-x-0">
        <!-- Logo Header -->
        <div class="h-20 flex items-center px-6 border-b border-[#162540] justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK" class="h-10 w-auto rounded-lg shadow-lg border border-sky-500/20 group-hover:border-sky-400 transition-colors">
            </a>
            <button onclick="toggleSidebar()" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <div class="px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                Gerenciador Geral
            </div>

            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-sky-600 to-blue-700 text-white shadow-lg shadow-sky-900/30 font-semibold' : 'text-slate-300 hover:bg-[#122038] hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-sky-400' }}"></i>
                <span>Dashboard</span>
            </a>

            <!-- Clientes -->
            <a href="{{ route('admin.clients.index') }}" 
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.clients.*') ? 'bg-gradient-to-r from-sky-600 to-blue-700 text-white shadow-lg shadow-sky-900/30 font-semibold' : 'text-slate-300 hover:bg-[#122038] hover:text-white' }}">
                <i data-lucide="users" class="w-5 h-5 {{ request()->routeIs('admin.clients.*') ? 'text-white' : 'text-sky-400' }}"></i>
                <span>Clientes</span>
            </a>

            <!-- Empréstimos -->
            <a href="{{ route('admin.loans.index') }}" 
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.loans.*') ? 'bg-gradient-to-r from-sky-600 to-blue-700 text-white shadow-lg shadow-sky-900/30 font-semibold' : 'text-slate-300 hover:bg-[#122038] hover:text-white' }}">
                <i data-lucide="banknote" class="w-5 h-5 {{ request()->routeIs('admin.loans.*') ? 'text-white' : 'text-sky-400' }}"></i>
                <div class="flex-1 flex items-center justify-between">
                    <span>Empréstimos</span>
                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-amber-500/20 text-amber-300 font-semibold border border-amber-500/30">PIX</span>
                </div>
            </a>

            <!-- Extrato (Envios e Recebimentos) -->
            <a href="{{ route('admin.statement.index') }}" 
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.statement.*') ? 'bg-gradient-to-r from-sky-600 to-blue-700 text-white shadow-lg shadow-sky-900/30 font-semibold' : 'text-slate-300 hover:bg-[#122038] hover:text-white' }}">
                <i data-lucide="arrow-left-right" class="w-5 h-5 {{ request()->routeIs('admin.statement.*') ? 'text-white' : 'text-sky-400' }}"></i>
                <span>Extrato Geral</span>
            </a>

            <div class="pt-6 px-3 pb-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                Integrações & Sistema
            </div>

            <!-- Gateway MisticPay -->
            <a href="{{ route('admin.settings.misticpay') }}" 
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.settings.misticpay') ? 'bg-gradient-to-r from-sky-600 to-blue-700 text-white shadow-lg shadow-sky-900/30 font-semibold' : 'text-slate-300 hover:bg-[#122038] hover:text-white' }}">
                <i data-lucide="zap" class="w-5 h-5 {{ request()->routeIs('admin.settings.misticpay') ? 'text-white' : 'text-amber-400' }}"></i>
                <div class="flex-1 flex items-center justify-between">
                    <span>Gateway MisticPay</span>
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                </div>
            </a>

            <!-- Acesso ao Portal do Cliente (Para Teste) -->
            <a href="{{ route('client.dashboard') }}" target="_blank"
               class="flex items-center gap-3.5 px-3.5 py-3 rounded-xl text-sm font-medium text-slate-400 hover:text-sky-300 hover:bg-[#122038] transition-all">
                <i data-lucide="external-link" class="w-5 h-5 text-slate-500"></i>
                <div class="flex-1 flex items-center justify-between">
                    <span>Ver Área do Cliente</span>
                    <span class="text-[11px] text-slate-500">↗</span>
                </div>
            </a>
        </nav>

        <!-- Gateway Status Box in Sidebar -->
        <div class="p-4 mx-4 mb-4 rounded-xl bg-[#0F1D38]/80 border border-sky-500/20">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-300 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-400"></i> MisticPay PIX
                </span>
                @php
                    $isMock = \App\Models\GatewaySetting::get('misticpay_mock', 'true') === 'true';
                @endphp
                <span class="text-[10px] px-2 py-0.5 rounded font-medium {{ $isMock ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                    {{ $isMock ? 'SIMULAÇÃO' : 'PRODUÇÃO' }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 leading-snug">
                Cash-in (QR Code) e Cash-out (Envios) habilitados.
            </p>
        </div>

        <!-- User Footer -->
        <div class="p-4 border-t border-[#162540] flex items-center justify-between bg-[#08101E]">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-sky-600 to-amber-500 flex items-center justify-center font-bold text-white text-sm shadow-md">
                    {{ substr(auth()->user()->name ?? 'AS', 0, 2) }}
                </div>
                <div class="truncate">
                    <p class="text-xs font-semibold text-white truncate max-w-[120px]">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">Administrador</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Encerrar Sessão" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-72">
        <!-- Top Header -->
        <header class="h-20 bg-[#0A1324]/80 backdrop-blur-md border-b border-[#162540] sticky top-0 z-30 px-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div>
                    <h1 class="text-lg font-bold text-white flex items-center gap-2">
                        @yield('header-title', 'Painel Administrativo')
                    </h1>
                    <p class="text-xs text-slate-400 hidden sm:block">ASBANK • Sistema de Gestão Financeira e Empréstimos PIX</p>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.loans.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 font-bold text-xs uppercase tracking-wider shadow-lg shadow-amber-900/20 hover:from-amber-400 hover:to-amber-500 transition-all">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Novo Empréstimo</span>
                </a>
                <a href="{{ route('admin.clients.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600/20 text-sky-300 border border-sky-500/30 hover:bg-sky-600/30 font-semibold text-xs transition-all">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Novo Cliente</span>
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-6 pt-6">
            @if (session('success'))
                <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm mb-4 animate-fadeIn">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5"></i>
                    <div class="flex-1 font-medium">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-start gap-3 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm mb-4 animate-fadeIn">
                    <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5"></i>
                    <div class="flex-1 font-medium">{{ session('error') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="flex items-start gap-3 p-4 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-300 text-sm mb-4 animate-fadeIn">
                    <i data-lucide="info" class="w-5 h-5 text-sky-400 shrink-0 mt-0.5"></i>
                    <div class="flex-1 font-medium">{{ session('info') }}</div>
                </div>
            @endif
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-6">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="px-6 py-4 border-t border-[#162540] text-center text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
            <span>© {{ date('Y') }} ASBANK • Confiança que cresce. Todos os direitos reservados.</span>
            <span class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span> Gateway MisticPay Conectado
            </span>
        </footer>
    </div>

    <!-- Scripts -->
    <script>
        lucide.createIcons();

        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        function copyToClipboard(text, message = 'Copiado para a área de transferência!') {
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
