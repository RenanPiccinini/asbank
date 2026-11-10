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

    <style>
        .overflow-x-hidden { overflow-x: hidden; }
        .break-words { overflow-wrap: break-word; }
        .break-all { word-break: break-all; }
        .min-h-20 { min-height: 5rem; }
        .ml-auto { margin-left: auto; }
        .gap-x-3 { column-gap: 0.75rem; }
        .gap-y-1 { row-gap: 0.25rem; }
        .bg-\[\#0A1324\]\/90 { background-color: rgba(10, 19, 36, 0.9); }
        .bg-black\/30 { background-color: rgba(0, 0, 0, 0.3); }
        .text-amber-200 { color: #FDE68A; }
        @media (min-width: 640px) {
            .sm\:flex-initial { flex: 0 1 auto; }
            .sm\:p-6 { padding: 1.5rem; }
            .sm\:p-3\.5 { padding: 0.875rem; }
            .sm\:pt-6 { padding-top: 1.5rem; }
            .sm\:px-4 { padding-left: 1rem; padding-right: 1rem; }
            .sm\:gap-3 { gap: 0.75rem; }
            .sm\:gap-6 { gap: 1.5rem; }
            .sm\:justify-end { justify-content: flex-end; }
            .sm\:text-right { text-align: right; }
            .sm\:text-xl { font-size: 1.25rem; line-height: 1.75rem; }
            .sm\:text-xs { font-size: 0.75rem; line-height: 1rem; }
            .sm\:w-14 { width: 3.5rem; }
            .sm\:h-14 { height: 3.5rem; }
            .sm\:w-16 { width: 4rem; }
            .sm\:h-16 { height: 4rem; }
            .sm\:w-7 { width: 1.75rem; }
            .sm\:h-7 { height: 1.75rem; }
            .sm\:h-4 { height: 1rem; }
        }
        @media (min-width: 1024px) {
            .lg\:w-auto { width: auto; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#070D18] text-slate-100 min-h-screen flex overflow-x-hidden">

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
                Acesso Externo
            </div>

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
                    $isMock = app(\App\Services\MisticPayService::class)->isMockMode();
                @endphp
                <span class="text-[10px] px-2 py-0.5 rounded font-medium {{ $isMock ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' }}">
                    {{ $isMock ? 'SIMULAÇÃO' : 'PRODUÇÃO' }}
                </span>
            </div>
            <p class="text-[11px] text-slate-400 leading-snug">
                Cash-in (QR Code) e Cash-out (Envios) habilitados via .env.
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
        <header class="min-h-20 py-3 bg-[#0A1324]/90 backdrop-blur-md border-b border-[#162540] sticky top-0 z-30 px-4 sm:px-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-slate-300 bg-[#0F1D38] border border-[#162540] hover:text-white hover:bg-slate-800 shrink-0">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="min-w-0">
                    <h1 class="text-base sm:text-lg font-bold text-white flex items-center gap-2 truncate">
                        @yield('header-title', 'Painel Administrativo')
                    </h1>
                    <p class="text-[11px] sm:text-xs text-slate-400 truncate">ASBANK • Sistema de Gestão Financeira e Empréstimos PIX</p>
                </div>
            </div>

            <!-- Header Quick Actions -->
            <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <a href="{{ route('admin.loans.create') }}" 
                   class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 font-bold text-[11px] sm:text-xs uppercase tracking-wider shadow-lg shadow-amber-900/20 hover:from-amber-400 hover:to-amber-500 transition-all whitespace-nowrap">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    <span>Novo Empréstimo</span>
                </a>
                <a href="{{ route('admin.clients.create') }}" 
                   class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 rounded-xl bg-sky-600/20 text-sky-300 border border-sky-500/30 hover:bg-sky-600/30 font-semibold text-[11px] sm:text-xs transition-all whitespace-nowrap">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                    <span>Novo Cliente</span>
                </a>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="px-4 sm:px-6 pt-4 sm:pt-6">
            @if (session('success'))
                <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm mb-4 animate-fadeIn">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5"></i>
                    <div class="flex-1 font-medium break-words">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-start gap-3 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm mb-4 animate-fadeIn">
                    <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5"></i>
                    <div class="flex-1 font-medium break-words">{{ session('error') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="flex items-start gap-3 p-4 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-300 text-sm mb-4 animate-fadeIn">
                    <i data-lucide="info" class="w-5 h-5 text-sky-400 shrink-0 mt-0.5"></i>
                    <div class="flex-1 font-medium break-words">{{ session('info') }}</div>
                </div>
            @endif
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="px-4 sm:px-6 py-4 border-t border-[#162540] text-center text-xs text-slate-500 flex flex-col md:flex-row items-center justify-between gap-4">
            <span>© {{ date('Y') }} ASBANK • Confiança que cresce. Todos os direitos reservados.</span>

            <div class="flex flex-col sm:flex-row items-center gap-4">
                <span class="flex items-center gap-2 text-slate-400">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span> Gateway MisticPay Conectado
                </span>

                <div class="flex items-center gap-2.5 text-slate-400">
                    <span class="text-xs font-medium">Desenvolvido por</span>
                    <a href="https://piccininidigitalsolutions.com.br/" target="_blank" rel="noopener noreferrer"
                       title="Piccinini Digital Solutions"
                       class="inline-flex items-center bg-white rounded-xl p-1.5 shadow-md border border-sky-500/30 hover:border-sky-400 transition-all">
                        <img src="{{ asset('images/pds-logo.png') }}" alt="Piccinini Digital Solutions" class="h-9 w-auto object-contain rounded-lg">
                    </a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Global ASBANK Confirmation Modal -->
    <div id="asbankConfirmModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 hidden transition-opacity duration-200">
        <div class="bg-[#0A1324] border border-[#162540] rounded-3xl max-w-md w-full p-6 text-center shadow-2xl relative transform transition-all duration-200 scale-95" id="asbankConfirmBox">
            <button type="button" onclick="closeConfirmModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <!-- Dynamic Icon Container -->
            <div id="confirmIconContainer" class="w-16 h-16 rounded-2xl mx-auto flex items-center justify-center mb-4 bg-amber-500/10 text-amber-400 border border-amber-500/20">
                <i id="confirmIcon" data-lucide="help-circle" class="w-8 h-8"></i>
            </div>

            <h3 id="confirmTitle" class="text-lg font-bold text-white mb-2">Confirmação</h3>
            <p id="confirmMessage" class="text-xs text-slate-300 leading-relaxed mb-6"></p>

            <div class="flex items-center gap-3">
                <button type="button" onclick="closeConfirmModal()" class="flex-1 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition-colors">
                    Cancelar
                </button>
                <button type="button" id="confirmSubmitBtn" onclick="executeConfirmAction()" class="flex-1 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-950 bg-amber-500 hover:bg-amber-400 shadow-lg shadow-amber-900/30 transition-all">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Global ASBANK Toast Notification -->
    <div id="asbankToast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 flex items-center gap-3 px-4 py-3 rounded-2xl bg-[#0D1B2A] border border-emerald-500/30 text-white shadow-2xl pointer-events-none">
        <div id="toastIconWrapper" class="p-1.5 rounded-xl bg-emerald-500/20 text-emerald-400">
            <i data-lucide="check-circle-2" class="w-5 h-5"></i>
        </div>
        <span id="toastMessage" class="text-xs font-semibold text-emerald-300">Ação realizada com sucesso!</span>
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

        let pendingFormToSubmit = null;

        function confirmAction(event, title, message, icon = 'alert-triangle', colorScheme = 'amber') {
            event.preventDefault();
            pendingFormToSubmit = event.target.closest('form');

            document.getElementById('confirmTitle').innerText = title;
            document.getElementById('confirmMessage').innerText = message;

            const iconContainer = document.getElementById('confirmIconContainer');
            const submitBtn = document.getElementById('confirmSubmitBtn');

            if (colorScheme === 'rose') {
                iconContainer.className = 'w-16 h-16 rounded-2xl mx-auto flex items-center justify-center mb-4 bg-rose-500/10 text-rose-400 border border-rose-500/20';
                submitBtn.className = 'flex-1 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-rose-600 hover:bg-rose-500 shadow-lg shadow-rose-900/30 transition-all';
                submitBtn.innerText = 'Excluir';
            } else if (colorScheme === 'emerald') {
                iconContainer.className = 'w-16 h-16 rounded-2xl mx-auto flex items-center justify-center mb-4 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                submitBtn.className = 'flex-1 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-950 bg-emerald-400 hover:bg-emerald-300 shadow-lg shadow-emerald-900/30 transition-all';
                submitBtn.innerText = 'Confirmar';
            } else {
                iconContainer.className = 'w-16 h-16 rounded-2xl mx-auto flex items-center justify-center mb-4 bg-amber-500/10 text-amber-400 border border-amber-500/20';
                submitBtn.className = 'flex-1 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-950 bg-amber-500 hover:bg-amber-400 shadow-lg shadow-amber-900/30 transition-all';
                submitBtn.innerText = 'Confirmar Envio';
            }

            const iconEl = document.getElementById('confirmIcon');
            iconEl.setAttribute('data-lucide', icon);
            lucide.createIcons();

            const modal = document.getElementById('asbankConfirmModal');
            const box = document.getElementById('asbankConfirmBox');
            modal.classList.remove('hidden');
            setTimeout(() => {
                box.classList.remove('scale-95');
                box.classList.add('scale-100');
            }, 10);

            return false;
        }

        function closeConfirmModal() {
            const modal = document.getElementById('asbankConfirmModal');
            const box = document.getElementById('asbankConfirmBox');
            box.classList.remove('scale-100');
            box.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                pendingFormToSubmit = null;
            }, 150);
        }

        function executeConfirmAction() {
            if (pendingFormToSubmit) {
                const form = pendingFormToSubmit;
                closeConfirmModal();
                form.submit();
            }
        }

        function showToast(message, isError = false) {
            const toast = document.getElementById('asbankToast');
            const textEl = document.getElementById('toastMessage');
            const iconWrapper = document.getElementById('toastIconWrapper');

            textEl.innerText = message;
            if (isError) {
                toast.className = 'fixed bottom-6 right-6 z-50 transform translate-y-0 opacity-100 transition-all duration-300 flex items-center gap-3 px-4 py-3 rounded-2xl bg-[#1F1318] border border-rose-500/40 text-white shadow-2xl pointer-events-auto';
                iconWrapper.className = 'p-1.5 rounded-xl bg-rose-500/20 text-rose-400';
                textEl.className = 'text-xs font-semibold text-rose-300';
            } else {
                toast.className = 'fixed bottom-6 right-6 z-50 transform translate-y-0 opacity-100 transition-all duration-300 flex items-center gap-3 px-4 py-3 rounded-2xl bg-[#0D1B2A] border border-emerald-500/40 text-white shadow-2xl pointer-events-auto';
                iconWrapper.className = 'p-1.5 rounded-xl bg-emerald-500/20 text-emerald-400';
                textEl.className = 'text-xs font-semibold text-emerald-300';
            }

            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
                toast.classList.remove('translate-y-0', 'opacity-100');
            }, 3000);
        }

        function copyToClipboard(text, message = 'Copiado para a área de transferência!') {
            navigator.clipboard.writeText(text).then(() => {
                showToast(message);
            }).catch(err => {
                console.error('Falha ao copiar:', err);
                showToast('Falha ao copiar código', true);
            });
        }
    </script>
    @stack('scripts')
</body>
</html>

