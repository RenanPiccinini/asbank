<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ASBANK') }} - Confiança que cresce</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|jetbrains-mono:500,700&display=swap" rel="stylesheet" />

    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html {
            scroll-behavior: smooth;
        }

        :root {
            --bg-void: #040812;
            --bg-deep: #070D18;
            --bg-card: rgba(10, 19, 36, 0.76);
            --border-glass: rgba(56, 189, 248, 0.16);
        }

        body {
            background-color: var(--bg-void);
            color: #F1F5F9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            overflow-x: hidden;
            margin: 0;
        }

        .font-mono-tech {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Background Grid & Ambient Orbs */
        .tech-grid-bg {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background-image:
                linear-gradient(to right, rgba(56, 189, 248, 0.045) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(56, 189, 248, 0.045) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 85% 65% at 50% 20%, #000 35%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 85% 65% at 50% 20%, #000 35%, transparent 100%);
        }

        .parallax-orb {
            position: fixed;
            border-radius: 9999px;
            filter: blur(115px);
            pointer-events: none;
            z-index: 0;
            will-change: transform;
        }

        .orb-sky {
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.25), transparent 70%);
            top: -120px;
            left: -100px;
        }

        .orb-amber {
            width: 460px;
            height: 460px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.17), transparent 70%);
            top: 260px;
            right: -120px;
        }

        .orb-indigo {
            width: 560px;
            height: 560px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.14), transparent 70%);
            bottom: -180px;
            left: 25%;
        }

        /* Container & Header */
        .site-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        @media (min-width: 640px) {
            .site-container {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }

        .top-header {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(4, 8, 18, 0.78);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .top-header-inner {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .nav-links {
            display: none;
            align-items: center;
            gap: 1.75rem;
        }

        @media (min-width: 768px) {
            .nav-links {
                display: flex;
            }
        }

        .nav-link {
            font-size: 0.78rem;
            font-weight: 600;
            color: #CBD5E1;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .nav-link:hover {
            color: #38BDF8;
        }

        /* Hero Layout */
        .hero-section {
            padding-top: 3.5rem;
            padding-bottom: 5rem;
        }

        @media (min-width: 1024px) {
            .hero-section {
                padding-top: 5.5rem;
                padding-bottom: 6.5rem;
            }
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3.5rem;
            align-items: center;
        }

        @media (min-width: 1024px) {
            .hero-grid {
                grid-template-columns: 1.05fr 0.95fr;
                gap: 3rem;
            }
        }

        .hero-headline {
            font-size: 2.5rem;
            font-weight: 900;
            line-height: 1.06;
            letter-spacing: -0.025em;
            color: #FFFFFF;
            margin: 0;
        }

        @media (min-width: 640px) {
            .hero-headline {
                font-size: 3.65rem;
            }
        }

        .hero-gradient-text {
            background: linear-gradient(90deg, #38BDF8 0%, #60A5FA 48%, #FBBF24 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            padding-top: 1.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 520px;
        }

        /* 3D Perspective Stage */
        .stage-3d {
            perspective: 1400px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 2rem 0;
        }

        .hero-card-halo {
            position: absolute;
            width: 340px;
            height: 340px;
            border-radius: 9999px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.25), rgba(245, 158, 11, 0.14), transparent 70%);
            filter: blur(55px);
            pointer-events: none;
        }

        .card-3d-wrapper {
            position: relative;
            width: 100%;
            max-width: 440px;
            transform-style: preserve-3d;
            transition: transform 0.14s ease-out;
            will-change: transform;
            cursor: pointer;
        }

        @keyframes floatLevitate {
            0%, 100% { transform: translateY(0px) rotateX(4deg) rotateY(-6deg); }
            50% { transform: translateY(-14px) rotateX(7deg) rotateY(-2deg); }
        }

        .card-3d-idle {
            animation: floatLevitate 7s ease-in-out infinite;
        }

        .card-surface {
            position: relative;
            min-height: 268px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(135deg, rgba(16, 32, 62, 0.96) 0%, rgba(7, 13, 24, 0.98) 55%, rgba(16, 33, 62, 0.95) 100%);
            border: 1px solid rgba(56, 189, 248, 0.38);
            border-radius: 24px;
            box-shadow:
                0 32px 72px -15px rgba(0, 0, 0, 0.9),
                0 0 55px -10px rgba(14, 165, 233, 0.28),
                inset 0 1px 1px rgba(255, 255, 255, 0.16);
            transform-style: preserve-3d;
            overflow: hidden;
        }

        .card-holo-glare {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at var(--mx, 50%) var(--my, 50%), rgba(255, 255, 255, 0.18) 0%, rgba(56, 189, 248, 0.09) 28%, transparent 65%);
            pointer-events: none;
            mix-blend-mode: screen;
        }

        .depth-layer-1 { transform: translateZ(26px); }
        .depth-layer-2 { transform: translateZ(46px); }

        @keyframes badgeFloatA {
            0%, 100% { transform: translateZ(62px) translateY(0px); }
            50% { transform: translateZ(62px) translateY(-8px); }
        }

        @keyframes badgeFloatB {
            0%, 100% { transform: translateZ(52px) translateY(0px); }
            50% { transform: translateZ(52px) translateY(8px); }
        }

        .floating-widget-top {
            display: flex;
            position: absolute;
            top: -18px;
            right: 6px;
            z-index: 20;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 0.85rem;
            border-radius: 14px;
            border: 1px solid rgba(16, 185, 129, 0.45);
            animation: badgeFloatA 5.5s ease-in-out infinite;
            transform-style: preserve-3d;
        }

        .floating-widget-bottom {
            display: flex;
            position: absolute;
            bottom: -18px;
            left: 6px;
            z-index: 20;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 0.85rem;
            border-radius: 14px;
            border: 1px solid rgba(56, 189, 248, 0.45);
            animation: badgeFloatB 6.2s ease-in-out infinite;
            transform-style: preserve-3d;
        }

        @media (min-width: 640px) {
            .floating-widget-top {
                top: -26px;
                right: -18px;
                gap: 0.75rem;
                padding: 0.75rem 1rem;
                border-radius: 16px;
            }
            .floating-widget-bottom {
                bottom: -24px;
                left: -18px;
                gap: 0.75rem;
                padding: 0.75rem 1rem;
                border-radius: 16px;
            }
        }

        /* Mobile Scroll Nav Strip */
        .mobile-nav-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.55rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        @media (min-width: 768px) {
            .mobile-nav-strip {
                display: none;
            }
        }

        /* Glassmorphism Cards & Spotlight Hover */
        .glass-panel {
            background: var(--bg-card);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.09);
            box-shadow: 0 24px 60px -20px rgba(0, 0, 0, 0.78);
        }

        .tilt-feature-card {
            position: relative;
            background: linear-gradient(180deg, rgba(13, 24, 46, 0.88) 0%, rgba(8, 15, 29, 0.94) 100%);
            border: 1px solid rgba(56, 189, 248, 0.16);
            border-radius: 22px;
            transform-style: preserve-3d;
            transition: transform 0.18s ease-out, border-color 0.25s ease, box-shadow 0.25s ease;
            overflow: hidden;
        }

        .tilt-feature-card:hover {
            border-color: rgba(56, 189, 248, 0.5);
            box-shadow: 0 25px 50px -15px rgba(14, 165, 233, 0.28);
        }

        .tilt-feature-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(380px circle at var(--cx, 50%) var(--cy, 50%), rgba(56, 189, 248, 0.14), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .tilt-feature-card:hover::before {
            opacity: 1;
        }

        /* Simulator Layout */
        .sim-glow-frame {
            position: relative;
            border-radius: 28px;
            padding: 1.5px;
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.5), rgba(15, 29, 56, 0.35), rgba(245, 158, 11, 0.5));
            box-shadow: 0 35px 80px -25px rgba(0, 0, 0, 0.92), 0 0 60px -20px rgba(14, 165, 233, 0.22);
        }

        .sim-inner {
            background: rgba(7, 13, 24, 0.96);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 27px;
            padding: 1.5rem;
        }

        @media (min-width: 640px) {
            .sim-inner {
                padding: 2.5rem;
            }
        }

        @media (min-width: 1024px) {
            .sim-inner {
                padding: 3rem;
            }
        }

        .sim-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
            align-items: center;
        }

        @media (min-width: 1024px) {
            .sim-grid {
                grid-template-columns: 1.25fr 0.95fr;
                gap: 3rem;
            }
        }

        /* Feature & Timeline Grids */
        .tech-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .tech-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .tech-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .cta-banner {
            border-radius: 24px;
            background: linear-gradient(135deg, #0B1730 0%, #0E2044 50%, #0B1730 100%);
            border: 1px solid rgba(56, 189, 248, 0.28);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: space-between;
            gap: 2rem;
            box-shadow: 0 30px 65px -20px rgba(0, 0, 0, 0.85);
        }

        @media (min-width: 1024px) {
            .cta-banner {
                padding: 3.5rem;
                flex-direction: row;
                align-items: center;
            }
        }

        /* Custom Range Slider */
        input[type="range"].tech-slider {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: #0F1D38;
            outline: none;
            border: 1px solid rgba(56, 189, 248, 0.25);
        }

        input[type="range"].tech-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FBBF24, #D97706);
            border: 3px solid #040812;
            box-shadow: 0 0 18px rgba(245, 158, 11, 0.8);
            cursor: pointer;
            transition: transform 0.15s ease;
        }

        input[type="range"].tech-slider::-webkit-slider-thumb:hover {
            transform: scale(1.18);
        }

        input[type="range"].tech-slider.sky-thumb::-webkit-slider-thumb {
            background: linear-gradient(135deg, #38BDF8, #0284C7);
            box-shadow: 0 0 18px rgba(56, 189, 248, 0.8);
        }

        /* Scroll Reveal Animations */
        .reveal-up {
            opacity: 0;
            transform: translateY(32px) scale(0.985);
            transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal-up.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .preset-pill {
            padding: 6px 13px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid rgba(148, 163, 184, 0.2);
            background: rgba(7, 13, 24, 0.8);
            color: #94A3B8;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .preset-pill:hover, .preset-pill.active {
            border-color: rgba(245, 158, 11, 0.65);
            background: rgba(245, 158, 11, 0.15);
            color: #FCD34D;
        }

        .preset-pill.sky-active {
            border-color: rgba(56, 189, 248, 0.65);
            background: rgba(56, 189, 248, 0.15);
            color: #7DD3FC;
        }

        @keyframes shimmerLine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(220%); }
        }

        .shimmer-bar {
            position: relative;
            overflow: hidden;
        }

        .shimmer-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 45%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.28), transparent);
            animation: shimmerLine 3.6s infinite;
        }

        /* Explicit utility guarantees for standalone welcome page */
        @keyframes pulseGlow { 0%, 100% { opacity: 1; } 50% { opacity: 0.45; } }
        .animate-pulse { animation: pulseGlow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        .bg-\[\#040812\] { background-color: #040812; }
        .bg-sky-400 { background-color: #38BDF8; }
        .w-1\.5 { width: 0.375rem; }
        .h-1\.5 { height: 0.375rem; }
        .items-end { align-items: flex-end; }
        .leading-none { line-height: 1; }
        .mb-12 { margin-bottom: 3rem; }
        .pb-24 { padding-bottom: 6rem; }
        .px-7 { padding-left: 1.75rem; padding-right: 1.75rem; }
        .shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.25); }
        .space-y-2\.5 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.625rem; }
        @media (min-width: 640px) {
            .sm\:text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
            .sm\:text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        }
    </style>
</head>
<body class="antialiased selection:bg-amber-500 selection:text-slate-950 relative">

    <!-- Interactive 3D Particle Network Canvas -->
    <canvas id="bg-particles" style="position:fixed; inset:0; width:100%; height:100%; pointer-events:none; z-index:0; opacity:0.78;"></canvas>

    <!-- Parallax Ambient Orbs -->
    <div class="parallax-orb orb-sky" data-parallax="0.14"></div>
    <div class="parallax-orb orb-amber" data-parallax="-0.16"></div>
    <div class="parallax-orb orb-indigo" data-parallax="0.09"></div>

    <!-- Subtle Perspective Grid -->
    <div class="tech-grid-bg"></div>

    <!-- Top Glassmorphic Navigation -->
    <header class="top-header">
        <div class="site-container">
            <div class="top-header-inner">
                <a href="/" class="flex items-center gap-2.5 group" style="text-decoration:none;">
                    <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK - Confiança que cresce" class="h-9 sm:h-11 w-auto rounded-xl shadow-lg border border-sky-500/30">
                    <div>
                        <span class="font-black text-white tracking-tight text-sm sm:text-lg block leading-none">ASBANK</span>
                        <span class="text-[9px] sm:text-[10px] text-sky-400 font-semibold uppercase tracking-wider">Digital Banking PIX</span>
                    </div>
                </a>

                <nav class="nav-links">
                    <a href="#simulador" class="nav-link">Simulador</a>
                    <a href="#tecnologia" class="nav-link">Tecnologia PIX</a>
                    <a href="#como-funciona" class="nav-link">Como Funciona</a>
                    <a href="#seguranca" class="nav-link">Segurança</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-[11px] sm:text-xs uppercase tracking-wider shadow-lg transition-all flex items-center gap-1.5">
                                <i data-lucide="shield" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                <span>Gerenciador Admin</span>
                            </a>
                        @else
                            <a href="{{ route('client.dashboard') }}" class="px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider shadow-lg transition-all flex items-center gap-1.5">
                                <i data-lucide="user" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                <span>Meu Painel</span>
                            </a>
                        @endif
                    @else
                        <a href="#simulador" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-slate-700 hover:border-amber-400 text-slate-200 hover:text-amber-300 font-semibold text-[11px] sm:text-xs transition-all">
                            Simular
                        </a>
                        <a href="{{ route('login') }}" class="px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider shadow-lg transition-all flex items-center gap-1.5">
                            <i data-lucide="log-in" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                            <span>Acessar</span>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Mobile Navigation Strip (100% same links as PC) -->
            <div class="mobile-nav-strip">
                <a href="#simulador" class="nav-link">Simulador</a>
                <a href="#tecnologia" class="nav-link">Tecnologia PIX</a>
                <a href="#como-funciona" class="nav-link">Como Funciona</a>
                <a href="#seguranca" class="nav-link">Segurança</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main style="position: relative; z-index: 10;">

        <!-- HERO SECTION WITH 3D INTERACTIVE CARD & PARALLAX -->
        <section class="site-container hero-section">
            <div class="hero-grid">

                <!-- Left Copy -->
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-[#0A1324] border border-sky-500/30 text-sky-300 text-xs font-semibold shadow-lg">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>Infraestrutura Financeira PIX • Operação 24/7</span>
                    </div>

                    <h1 class="hero-headline">
                        ASBANK <br>
                        <span class="hero-gradient-text">
                            Confiança que cresce.
                        </span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl">
                        Crédito digital de alta performance com emissão instantânea de contratos inteligentes, análise automatizada de score e liquidação financeira em segundos diretamente na sua chave PIX.
                    </p>

                    <div class="flex flex-wrap items-center gap-4 pt-1">
                        <a href="#simulador" class="shimmer-bar px-7 py-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-xl transition-all inline-flex items-center gap-2">
                            <i data-lucide="calculator" class="w-4 h-4"></i>
                            <span>Simular Empréstimo</span>
                        </a>

                        @auth
                            <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('client.dashboard') }}" class="px-7 py-4 rounded-xl bg-[#0A1324] border border-sky-500/30 hover:border-sky-400 text-white font-bold text-xs uppercase tracking-wider transition-all inline-flex items-center gap-2">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-sky-400"></i>
                                <span>Ir para o Painel</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-7 py-4 rounded-xl bg-[#0A1324] border border-sky-500/30 hover:border-sky-400 text-white font-bold text-xs uppercase tracking-wider transition-all inline-flex items-center gap-2">
                                <i data-lucide="lock" class="w-4 h-4 text-sky-400"></i>
                                <span>Acessar Plataforma</span>
                            </a>
                        @endauth
                    </div>

                    <!-- Real-time Metrics Strip -->
                    <div class="hero-metrics-grid">
                        <div>
                            <div class="text-2xl sm:text-3xl font-black text-white font-mono-tech">&lt; 3s</div>
                            <div class="text-[11px] text-slate-400 font-medium mt-0.5">Transferência PIX</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-black text-sky-400 font-mono-tech">100%</div>
                            <div class="text-[11px] text-slate-400 font-medium mt-0.5">Contrato Digital</div>
                        </div>
                        <div>
                            <div class="text-2xl sm:text-3xl font-black text-amber-400 font-mono-tech">24/7</div>
                            <div class="text-[11px] text-slate-400 font-medium mt-0.5">Liquidação Automática</div>
                        </div>
                    </div>
                </div>

                <!-- Right 3D Interactive Banking Card Stage -->
                <div class="stage-3d">
                    <div class="hero-card-halo"></div>

                    <div id="hero-card-3d" class="card-3d-wrapper card-3d-idle">

                        <!-- Floating Widget Top Right (Z-Depth 62px) -->
                        <div class="floating-widget-top glass-panel shadow-2xl">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">PIX Recebido</div>
                                <div class="text-sm font-black text-white font-mono-tech">+ R$ 15.000,00</div>
                            </div>
                        </div>

                        <!-- Main 3D Metallic Digital Card -->
                        <div class="card-surface">
                            <div class="card-holo-glare" id="card-glare"></div>

                            <!-- Card Header -->
                            <div class="flex items-center justify-between depth-layer-1">
                                <div class="flex items-center gap-3">
                                    <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK" class="h-9 w-auto rounded-lg border border-sky-400/30 shadow">
                                    <div>
                                        <span class="text-sm font-black tracking-wider text-white block leading-none">ASBANK</span>
                                        <span class="text-[10px] font-mono-tech text-amber-400 tracking-widest">INFINITY BLACK</span>
                                    </div>
                                </div>
                                <div class="px-2.5 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-[10px] font-bold text-emerald-300 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>PIX ATIVO</span>
                                </div>
                            </div>

                            <!-- EMV Chip & Contactless Icon -->
                            <div class="flex items-center justify-between my-6 depth-layer-2">
                                <div class="flex items-center gap-3">
                                    <svg class="h-8 rounded-md shadow-inner" style="width: 2.75rem;" viewBox="0 0 48 34" fill="none">
                                        <rect width="48" height="34" rx="6" fill="url(#goldChip)"/>
                                        <path d="M16 0V34M32 0V34M0 12H16M32 12H48M0 22H16M32 22H48" stroke="#78350F" stroke-width="1.2" stroke-opacity="0.55"/>
                                        <defs>
                                            <linearGradient id="goldChip" x1="0" y1="0" x2="48" y2="34" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#FDE68A"/>
                                                <stop offset="0.5" stop-color="#F59E0B"/>
                                                <stop offset="1" stop-color="#B45309"/>
                                            </linearGradient>
                                        </defs>
                                    </svg>
                                    <i data-lucide="wifi" class="w-5 h-5 text-slate-400" style="transform: rotate(90deg);"></i>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 block uppercase tracking-wider">Crédito Simulado</span>
                                    <span class="text-xl font-black text-white font-mono-tech" id="heroCardAmount">R$ 5.000,00</span>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="flex items-end justify-between depth-layer-1 pt-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-widest block">Identificador Digital</span>
                                    <span class="text-xs sm:text-sm font-mono-tech text-sky-300 tracking-widest">ASB •••• •••• 2026</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 uppercase block">Liquidação</span>
                                    <span class="text-xs font-bold text-amber-400 flex items-center gap-1">
                                        <i data-lucide="zap" class="w-3.5 h-3.5"></i> Instantânea
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Widget Bottom Left (Z-Depth 52px) -->
                        <div class="floating-widget-bottom glass-panel shadow-2xl">
                            <div class="w-9 h-9 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center shrink-0">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="text-[10px] text-sky-400 font-bold uppercase tracking-wider">Score Antifraude</div>
                                <div class="text-xs font-bold text-white">Aprovação Digital • 940 pts</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- INTERACTIVE 3D SIMULATOR SECTION -->
        <section id="simulador" class="site-container py-16 reveal-up" style="padding-bottom: 5rem;">
            <div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-[11px] font-bold uppercase tracking-wider">
                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i> Simulador em Tempo Real
                </span>
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                    Planeje seu crédito com <span class="text-amber-400">transparência total</span>
                </h2>
                <p class="text-sm text-slate-400">
                    Ajuste o valor desejado e o prazo para visualizar as parcelas e a composição do contrato antes de contratar.
                </p>
            </div>

            <div class="sim-glow-frame">
                <div class="sim-inner">
                    <div class="sim-grid">

                        <!-- Controls Column -->
                        <div class="space-y-8">
                            <!-- Amount Slider -->
                            <div class="space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <label for="rangeAmount" class="text-sm font-bold text-slate-200 flex items-center gap-2">
                                        <i data-lucide="wallet" class="w-4 h-4 text-amber-400"></i>
                                        Quanto você precisa?
                                    </label>
                                    <div class="text-2xl sm:text-3xl font-black text-white font-mono-tech" id="rangeAmountLabel">
                                        R$ 5.000,00
                                    </div>
                                </div>

                                <input type="range" id="rangeAmount" min="500" max="50000" step="500" value="5000"
                                       oninput="updateSimRange()" class="tech-slider">

                                <div class="flex flex-wrap items-center gap-2 pt-1">
                                    <button type="button" onclick="setAmountPreset(1000, this)" class="preset-pill">R$ 1.000</button>
                                    <button type="button" onclick="setAmountPreset(5000, this)" class="preset-pill active">R$ 5.000</button>
                                    <button type="button" onclick="setAmountPreset(15000, this)" class="preset-pill">R$ 15.000</button>
                                    <button type="button" onclick="setAmountPreset(30000, this)" class="preset-pill">R$ 30.000</button>
                                    <button type="button" onclick="setAmountPreset(50000, this)" class="preset-pill">R$ 50.000</button>
                                </div>
                            </div>

                            <!-- Months Slider -->
                            <div class="space-y-4 pt-6" style="border-top: 1px solid rgba(255,255,255,0.08);">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <label for="rangeMonths" class="text-sm font-bold text-slate-200 flex items-center gap-2">
                                        <i data-lucide="calendar" class="w-4 h-4 text-sky-400"></i>
                                        Em quantas parcelas deseja pagar?
                                    </label>
                                    <div class="text-2xl sm:text-3xl font-black text-sky-400 font-mono-tech" id="rangeMonthsLabel">
                                        12 meses
                                    </div>
                                </div>

                                <input type="range" id="rangeMonths" min="1" max="36" step="1" value="12"
                                       oninput="updateSimRange()" class="tech-slider sky-thumb">

                                <div class="flex flex-wrap items-center gap-2 pt-1">
                                    <button type="button" onclick="setMonthsPreset(3, this)" class="preset-pill">3x</button>
                                    <button type="button" onclick="setMonthsPreset(6, this)" class="preset-pill">6x</button>
                                    <button type="button" onclick="setMonthsPreset(12, this)" class="preset-pill sky-active">12x</button>
                                    <button type="button" onclick="setMonthsPreset(24, this)" class="preset-pill">24x</button>
                                    <button type="button" onclick="setMonthsPreset(36, this)" class="preset-pill">36x</button>
                                </div>
                            </div>

                            <!-- Distribution Bar -->
                            <div class="p-4 rounded-2xl bg-[#0A1324] space-y-2.5" style="border: 1px solid rgba(255,255,255,0.06);">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-400">Composição do Contrato</span>
                                    <span class="font-mono-tech text-sky-300 font-semibold">Taxa: 2.50% a.m.</span>
                                </div>
                                <div class="w-full h-2.5 rounded-full bg-slate-900 overflow-hidden flex">
                                    <div id="barPrincipal" class="h-full bg-gradient-to-r from-sky-500 to-blue-600 transition-all duration-300" style="width: 77%;"></div>
                                    <div id="barInterest" class="h-full bg-gradient-to-r from-amber-500 to-amber-400 transition-all duration-300" style="width: 23%;"></div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-400">
                                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-sky-400"></span> Capital Principal (<span id="pctPrincipal">77%</span>)</span>
                                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Encargos (<span id="pctInterest">23%</span>)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Live Summary Result Card -->
                        <div>
                            <div class="tilt-feature-card p-6 sm:p-8 space-y-6" data-tilt-card style="border-color: rgba(56,189,248,0.32);">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Parcela Mensal Estimada</span>
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-bold border border-emerald-500/30">
                                        Liberação PIX
                                    </span>
                                </div>

                                <div>
                                    <div class="text-3xl sm:text-4xl font-black text-amber-400 font-mono-tech tracking-tight" id="resParcel">
                                        12x de R$ 541,67
                                    </div>
                                    <div class="text-xs text-slate-400 mt-1" id="resFirstDue">
                                        1º vencimento estimado em 30 dias
                                    </div>
                                </div>

                                <div class="space-y-3 pt-4 text-xs" style="border-top: 1px solid rgba(255,255,255,0.08);">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-400">Valor Creditado na Conta</span>
                                        <span class="font-bold text-white font-mono-tech" id="resAmount">R$ 5.000,00</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-400">Encargos Totais do Período</span>
                                        <span class="font-semibold text-amber-300 font-mono-tech" id="resInterest">+ R$ 1.500,00</span>
                                    </div>
                                    <div class="flex items-center justify-between pt-2 text-sm" style="border-top: 1px solid rgba(255,255,255,0.06);">
                                        <span class="font-bold text-slate-200">Total a Pagar</span>
                                        <span class="font-black text-sky-400 font-mono-tech" id="resTotal">R$ 6.500,00</span>
                                    </div>
                                </div>

                                <a id="btnContratarViaPix" href="{{ route('loan.apply', ['amount' => 5000, 'installments' => 12]) }}"
                                   class="shimmer-bar w-full py-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2 shadow-lg transition-all">
                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                    <span>Contratar Agora via PIX</span>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- 3D TECHNOLOGY SHOWCASE -->
        <section id="tecnologia" class="site-container py-16 reveal-up" style="padding-bottom: 5rem;">
            <div class="mb-12 space-y-3 max-w-2xl">
                <span class="text-xs font-bold text-sky-400 uppercase tracking-widest block">Tecnologia Bancária</span>
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                    Arquitetura financeira de <span class="text-sky-400">alta velocidade</span>
                </h2>
                <p class="text-sm text-slate-400">
                    Cada operação é processada de ponta a ponta via API bancária com validação antifraude e rastreabilidade instantânea.
                </p>
            </div>

            <div class="tech-grid">
                <div class="tilt-feature-card p-6 space-y-4" data-tilt-card>
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center justify-center depth-layer-1">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white depth-layer-1">Desembolso PIX (Cash-Out)</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Envio automatizado do valor aprovado direto para a chave PIX do cliente com comprovante End-to-End do Banco Central.
                    </p>
                </div>

                <div class="tilt-feature-card p-6 space-y-4" data-tilt-card>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center depth-layer-1">
                        <i data-lucide="qr-code" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white depth-layer-1">Cobrança PIX (Cash-In)</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        QR Code dinâmico e Copia e Cola gerados sob demanda para cada parcela, com baixa automática em segundos via Webhooks.
                    </p>
                </div>

                <div class="tilt-feature-card p-6 space-y-4" data-tilt-card>
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/30 text-sky-400 flex items-center justify-center depth-layer-1">
                        <i data-lucide="file-check" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white depth-layer-1">Contratos & Score</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Emissão instantânea de Instrumento Particular de Mútuo Financeiro digital com parecer de crédito e cronograma anexo.
                    </p>
                </div>

                <div class="tilt-feature-card p-6 space-y-4" data-tilt-card>
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/30 text-blue-400 flex items-center justify-center depth-layer-1">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-white depth-layer-1">Conciliação Real-Time</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Extrato financeiro unificado de entradas e saídas com sincronização direta de status e auditoria completa.
                    </p>
                </div>
            </div>
        </section>

        <!-- PARALLAX TIMELINE: HOW IT WORKS -->
        <section id="como-funciona" class="site-container py-12 reveal-up" style="padding-bottom: 5rem;">
            <div class="glass-panel rounded-3xl p-8 relative overflow-hidden">
                <div class="text-center max-w-2xl mx-auto mb-12 space-y-2">
                    <span class="text-xs font-bold text-amber-400 uppercase tracking-widest">Jornada 100% Digital</span>
                    <h2 class="text-3xl font-black text-white">Do contrato ao PIX na conta em 4 passos</h2>
                </div>

                <div class="tech-grid">
                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-blue-700 text-white font-black text-sm flex items-center justify-center shadow-lg font-mono-tech">01</div>
                        <h4 class="font-bold text-white text-sm">Simulação Transparente</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Escolha o valor e a quantidade de parcelas ideais para o seu fluxo financeiro.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-blue-700 text-white font-black text-sm flex items-center justify-center shadow-lg font-mono-tech">02</div>
                        <h4 class="font-bold text-white text-sm">Análise & Contrato Digital</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Validação de cadastro, score de crédito e geração automática do contrato digital.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-600 text-slate-950 font-black text-sm flex items-center justify-center shadow-lg font-mono-tech">03</div>
                        <h4 class="font-bold text-white text-sm">Desembolso PIX Imediato</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Transferência instantânea direto para a chave PIX cadastrada sem burocracia.</p>
                    </div>

                    <div class="space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-slate-950 font-black text-sm flex items-center justify-center shadow-lg font-mono-tech">04</div>
                        <h4 class="font-bold text-white text-sm">Gestão pelo Portal</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">Acompanhe parcelas, baixe contratos e quite via QR Code PIX com confirmação ao vivo.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECURITY & FINAL CTA -->
        <section id="seguranca" class="site-container pb-24 reveal-up">
            <div class="cta-banner">
                <div class="space-y-4 max-w-2xl">
                    <div class="inline-flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                        <i data-lucide="shield-check" class="w-4 h-4"></i> Segurança Bancária & Conformidade
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-black text-white leading-tight">
                        Pronto para operar com agilidade e controle total?
                    </h2>
                    <p class="text-sm text-slate-300">
                        Acesse sua conta ASBANK para acompanhar contratos ativos, consultar comprovantes End-to-End e realizar pagamentos instantâneos via PIX.
                    </p>
                </div>

                <div class="shrink-0">
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="shimmer-bar px-8 py-4 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-xl transition-all inline-flex items-center gap-2">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>{{ auth()->check() ? 'Abrir Meu Painel' : 'Acessar Minha Conta' }}</span>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="relative z-10 bg-[#040812] py-8 text-center text-xs text-slate-500" style="border-top: 1px solid rgba(255,255,255,0.08);">
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

        // 1. REAL-TIME CREDIT SIMULATOR
        function setAmountPreset(val, btn) {
            document.getElementById('rangeAmount').value = val;
            document.querySelectorAll('#simulador .preset-pill').forEach(el => {
                if (el.innerText.includes('R$')) el.classList.remove('active');
            });
            if (btn) btn.classList.add('active');
            updateSimRange();
        }

        function setMonthsPreset(val, btn) {
            document.getElementById('rangeMonths').value = val;
            document.querySelectorAll('#simulador .preset-pill').forEach(el => {
                if (el.innerText.includes('x')) el.classList.remove('sky-active');
            });
            if (btn) btn.classList.add('sky-active');
            updateSimRange();
        }

        function updateSimRange() {
            const amount = parseFloat(document.getElementById('rangeAmount').value) || 5000;
            const months = parseInt(document.getElementById('rangeMonths').value) || 12;
            const rate = 0.025; // 2.50% a.m.

            const totalInterest = amount * rate * months;
            const total = amount + totalInterest;
            const parcel = total / months;

            const fmt = (n) => n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            document.getElementById('rangeAmountLabel').innerText = 'R$ ' + fmt(amount);
            document.getElementById('rangeMonthsLabel').innerText = months + (months === 1 ? ' mês' : ' meses');
            document.getElementById('resParcel').innerText = months + 'x de R$ ' + fmt(parcel);
            document.getElementById('resAmount').innerText = 'R$ ' + fmt(amount);
            document.getElementById('resInterest').innerText = '+ R$ ' + fmt(totalInterest);
            document.getElementById('resTotal').innerText = 'R$ ' + fmt(total);

            const pctPrincipal = Math.round((amount / total) * 100);
            const pctInterest = 100 - pctPrincipal;
            document.getElementById('barPrincipal').style.width = pctPrincipal + '%';
            document.getElementById('barInterest').style.width = pctInterest + '%';
            document.getElementById('pctPrincipal').innerText = pctPrincipal + '%';
            document.getElementById('pctInterest').innerText = pctInterest + '%';

            const heroAmt = document.getElementById('heroCardAmount');
            if (heroAmt) {
                heroAmt.innerText = 'R$ ' + fmt(amount);
            }

            const due = new Date();
            due.setDate(due.getDate() + 30);
            document.getElementById('resFirstDue').innerText = '1º vencimento estimado em ' + due.toLocaleDateString('pt-BR');

            const applyBtn = document.getElementById('btnContratarViaPix');
            if (applyBtn) {
                applyBtn.href = "{{ route('loan.apply') }}?amount=" + amount + "&installments=" + months;
            }
        }

        // 2. 3D INTERACTIVE HERO CARD TILT + HOLOGRAPHIC GLARE
        const heroCard = document.getElementById('hero-card-3d');
        const glare = document.getElementById('card-glare');

        if (heroCard && window.matchMedia('(pointer: fine)').matches) {
            heroCard.addEventListener('mouseenter', () => {
                heroCard.classList.remove('card-3d-idle');
            });

            heroCard.addEventListener('mousemove', (e) => {
                const rect = heroCard.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -14;
                const rotateY = ((x - centerX) / centerX) * 14;

                heroCard.style.transform = `rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) scale3d(1.03, 1.03, 1.03)`;

                if (glare) {
                    const mx = (x / rect.width) * 100;
                    const my = (y / rect.height) * 100;
                    glare.style.setProperty('--mx', mx + '%');
                    glare.style.setProperty('--my', my + '%');
                }
            });

            heroCard.addEventListener('mouseleave', () => {
                heroCard.style.transform = '';
                heroCard.classList.add('card-3d-idle');
            });
        }

        // 3. 3D TILT & SPOTLIGHT ON FEATURE CARDS
        document.querySelectorAll('[data-tilt-card]').forEach(card => {
            if (!window.matchMedia('(pointer: fine)').matches) return;

            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const rx = ((y - rect.height / 2) / (rect.height / 2)) * -5;
                const ry = ((x - rect.width / 2) / (rect.width / 2)) * 5;

                card.style.transform = `perspective(900px) rotateX(${rx.toFixed(2)}deg) rotateY(${ry.toFixed(2)}deg) translateY(-4px)`;
                card.style.setProperty('--cx', x + 'px');
                card.style.setProperty('--cy', y + 'px');
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });

        // 4. SCROLL PARALLAX & INTERSECTION REVEAL
        const parallaxItems = document.querySelectorAll('[data-parallax]');
        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;
            parallaxItems.forEach(item => {
                const speed = parseFloat(item.dataset.parallax) || 0.1;
                item.style.transform = `translate3d(0, ${(scrollY * speed).toFixed(1)}px, 0)`;
            });
        }, { passive: true });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.reveal-up').forEach(el => observer.observe(el));

        // 5. INTERACTIVE CANVAS FINTECH CONSTELLATION
        (function initParticles() {
            const canvas = document.getElementById('bg-particles');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let width = canvas.width = window.innerWidth;
            let height = canvas.height = window.innerHeight;

            const count = Math.min(55, Math.floor(width / 28));
            const nodes = Array.from({ length: count }, (_, i) => ({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.42,
                vy: (Math.random() - 0.5) * 0.42,
                r: Math.random() * 1.8 + 0.8,
                gold: i % 5 === 0
            }));

            let mouse = { x: -9999, y: -9999 };
            window.addEventListener('mousemove', e => {
                mouse.x = e.clientX;
                mouse.y = e.clientY;
            }, { passive: true });

            window.addEventListener('resize', () => {
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
            });

            function draw() {
                ctx.clearRect(0, 0, width, height);

                for (let i = 0; i < nodes.length; i++) {
                    const n = nodes[i];
                    n.x += n.vx;
                    n.y += n.vy;

                    if (n.x < 0 || n.x > width) n.vx *= -1;
                    if (n.y < 0 || n.y > height) n.vy *= -1;

                    const dxm = n.x - mouse.x;
                    const dym = n.y - mouse.y;
                    const distMouse = Math.hypot(dxm, dym);
                    if (distMouse < 140 && distMouse > 0) {
                        n.x += (dxm / distMouse) * 0.5;
                        n.y += (dym / distMouse) * 0.5;
                    }

                    ctx.beginPath();
                    ctx.arc(n.x, n.y, n.r, 0, Math.PI * 2);
                    ctx.fillStyle = n.gold ? 'rgba(251, 191, 36, 0.7)' : 'rgba(56, 189, 248, 0.65)';
                    ctx.fill();

                    for (let j = i + 1; j < nodes.length; j++) {
                        const m = nodes[j];
                        const dist = Math.hypot(n.x - m.x, n.y - m.y);
                        if (dist < 135) {
                            ctx.beginPath();
                            ctx.moveTo(n.x, n.y);
                            ctx.lineTo(m.x, m.y);
                            const alpha = (1 - dist / 135) * 0.18;
                            ctx.strokeStyle = (n.gold || m.gold)
                                ? `rgba(245, 158, 11, ${alpha})`
                                : `rgba(56, 189, 248, ${alpha})`;
                            ctx.lineWidth = 0.8;
                            ctx.stroke();
                        }
                    }
                }
                requestAnimationFrame(draw);
            }
            draw();
        })();

        document.addEventListener('DOMContentLoaded', updateSimRange);
    </script>
</body>
</html>
