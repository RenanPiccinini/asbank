<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Solicitar Crédito via PIX • ASBANK</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            background-color: #050A14;
            color: #F8FAFC;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            margin: 0;
        }
        .site-container {
            width: 100%;
            max-width: 1220px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        @media (min-width: 640px) {
            .site-container { padding-left: 1.5rem; padding-right: 1.5rem; }
        }
        .apply-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            align-items: start;
        }
        @media (min-width: 1024px) {
            .apply-grid {
                grid-template-columns: minmax(0, 1fr) 360px;
                gap: 2rem;
            }
            .apply-sidebar {
                position: sticky;
                top: 6rem;
            }
        }
        .fields-grid-2 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .fields-grid-3 {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .fields-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .fields-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .span-2-sm { grid-column: span 2 / span 2; }
        }
        .glass-section {
            background: rgba(10, 19, 36, 0.9);
            border: 1px solid rgba(56, 189, 248, 0.16);
            border-radius: 1.25rem;
            padding: 1.25rem;
            box-shadow: 0 20px 50px -15px rgba(0, 0, 0, 0.55);
        }
        @media (min-width: 640px) {
            .glass-section { padding: 1.75rem; }
        }
        .asbank-input {
            width: 100%;
            background: #070D18;
            border: 1px solid #1E3252;
            color: #F8FAFC;
            border-radius: 0.75rem;
            padding: 0.75rem 0.95rem;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .asbank-input:focus {
            outline: none;
            border-color: #38BDF8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.16);
        }
        .asbank-input::placeholder { color: #64748B; }
        .asbank-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #CBD5E1;
            margin-bottom: 0.4rem;
        }
        .req-mark { color: #F59E0B; }
        .quick-pill {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.28rem 0.65rem;
            border-radius: 9999px;
            background: rgba(14, 165, 233, 0.12);
            border: 1px solid rgba(56, 189, 248, 0.25);
            color: #7DD3FC;
            cursor: pointer;
            transition: all 0.2s;
        }
        .quick-pill:hover {
            background: rgba(14, 165, 233, 0.25);
            color: #FFFFFF;
        }
        .hero-bg-glow {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background:
                radial-gradient(circle at 12% 10%, rgba(14, 165, 233, 0.12), transparent 40%),
                radial-gradient(circle at 88% 85%, rgba(245, 158, 11, 0.08), transparent 40%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #38BDF8 0%, #7DD3FC 45%, #FBBF24 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .summary-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.85rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .summary-line:last-child { border-bottom: none; }
        .bg-\[\#040812\] { background-color: #040812; }
        .bg-\[\#050A14\]\/90 { background-color: rgba(5, 10, 20, 0.9); }
        .bg-amber-500\/15 { background-color: rgba(245, 158, 11, 0.15); }
        .bg-emerald-500\/15 { background-color: rgba(16, 185, 129, 0.15); }
        .bg-sky-500\/15 { background-color: rgba(14, 165, 233, 0.15); }
        .border-\[\#1E3252\] { border-color: #1E3252; }
        .border-rose-500\/30 { border-color: rgba(244, 63, 94, 0.3); }
        .border-sky-500\/15 { border-color: rgba(14, 165, 233, 0.15); }
        .border-white\/10 { border-color: rgba(255, 255, 255, 0.1); }
        .hover\:border-sky-500\/40:hover { border-color: rgba(14, 165, 233, 0.4); }
        .list-disc { list-style-type: disc; }
        .pl-5 { padding-left: 1.25rem; }
        .text-rose-200 { color: #FECDD3; }
        .text-rose-300\/90 { color: rgba(253, 164, 175, 0.9); }
        .text-sky-500 { color: #0EA5E9; }
        @media (min-width: 640px) {
            .sm\:h-10 { height: 2.5rem; }
            .sm\:h-20 { height: 5rem; }
            .sm\:py-12 { padding-top: 3rem; padding-bottom: 3rem; }
            .sm\:text-4xl { font-size: 2.25rem; line-height: 2.5rem; }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">
    <div class="hero-bg-glow"></div>

    <!-- Header -->
    <header class="sticky top-0 z-40 border-b border-sky-500/15 bg-[#050A14]/90 backdrop-blur-md">
        <div class="site-container h-16 sm:h-20 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK" class="h-9 sm:h-10 w-auto rounded-xl border border-sky-500/30 shadow-md">
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('home') }}#simulador" class="px-3 sm:px-4 py-2 rounded-xl bg-[#0A1324] border border-[#1E3252] text-xs font-semibold text-slate-300 hover:text-white hover:border-sky-500/40 transition flex items-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-sky-400"></i>
                    <span class="hidden sm:inline">Voltar ao Simulador</span>
                    <span class="sm:hidden">Voltar</span>
                </a>
                <a href="{{ route('login') }}" class="px-3 sm:px-4 py-2 rounded-xl bg-sky-500/10 border border-sky-500/30 text-xs font-bold text-sky-300 hover:bg-sky-500/20 transition flex items-center gap-1.5">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Já sou Cliente</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Form Section -->
    <main class="relative z-10 flex-1 py-8 sm:py-12">
        <div class="site-container space-y-6">

            <!-- Title Banner -->
            <div class="space-y-2 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold uppercase tracking-wider">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Solicitação Digital de Crédito PIX
                </div>
                <h1 class="text-2xl sm:text-4xl font-black text-white leading-tight">
                    Finalize seus dados para <span class="gradient-text">análise e liberação</span>
                </h1>
                <p class="text-sm text-slate-400">
                    Preencha seu cadastro completo e crie sua senha de acesso. Sua conta permanecerá em análise até a aprovação pela nossa equipe e liberação via WhatsApp.
                </p>
            </div>

            <!-- Alerts -->
            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-200 text-sm">
                    <div class="flex items-center gap-2 font-bold text-rose-300 mb-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        <span>Revise os campos abaixo antes de enviar sua proposta:</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-1 text-xs text-rose-300/90">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('loan.apply.store') }}" method="POST" class="apply-grid">
                @csrf

                <!-- LEFT COLUMN: Complete Application Form -->
                <div class="space-y-6">

                    <!-- 1. Condições Simuladas -->
                    <section class="glass-section space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-white/10">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center shrink-0">
                                <i data-lucide="sliders-horizontal" class="w-5 h-5 text-amber-400"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-white">1. Condições do Empréstimo Solicitado</h2>
                                <p class="text-xs text-slate-400">Valores importados da sua simulação (sujeitos a ajuste ou contraproposta na análise).</p>
                            </div>
                        </div>

                        <div class="fields-grid-3">
                            <div>
                                <label class="asbank-label" for="inputAmount">Valor Desejado (R$) <span class="req-mark">*</span></label>
                                <input type="number" step="0.01" min="0.01" max="1000000" name="amount" id="inputAmount"
                                       value="{{ old('amount', number_format($amount, 2, '.', '')) }}"
                                       required class="asbank-input font-bold text-amber-300" oninput="recalcProposalSummary()">
                            </div>

                            <div>
                                <label class="asbank-label" for="inputInstallments">Parcelas (meses) <span class="req-mark">*</span></label>
                                <input type="number" min="1" max="48" name="installments_count" id="inputInstallments"
                                       value="{{ old('installments_count', $installmentsCount) }}"
                                       required class="asbank-input font-bold text-sky-300" oninput="recalcProposalSummary()">
                            </div>

                            <div>
                                <label class="asbank-label" for="loan_purpose">Finalidade do Crédito <span class="req-mark">*</span></label>
                                <select name="loan_purpose" id="loan_purpose" required class="asbank-input">
                                    @php $oldPurpose = old('loan_purpose', 'Capital de Giro / Pessoal'); @endphp
                                    <option value="Pessoal / Emergência" {{ $oldPurpose === 'Pessoal / Emergência' ? 'selected' : '' }}>Uso Pessoal / Emergência</option>
                                    <option value="Capital de Giro / Negócios" {{ $oldPurpose === 'Capital de Giro / Negócios' ? 'selected' : '' }}>Capital de Giro / Negócios</option>
                                    <option value="Quitação de Dívidas" {{ $oldPurpose === 'Quitação de Dívidas' ? 'selected' : '' }}>Quitação de Dívidas</option>
                                    <option value="Investimento / Equipamentos" {{ $oldPurpose === 'Investimento / Equipamentos' ? 'selected' : '' }}>Investimento / Equipamentos</option>
                                    <option value="Capital de Giro / Pessoal" {{ $oldPurpose === 'Capital de Giro / Pessoal' ? 'selected' : '' }}>Outros Fins</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- 2. Dados Pessoais & Acesso ao Portal -->
                    <section class="glass-section space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-white/10">
                            <div class="w-9 h-9 rounded-xl bg-sky-500/15 border border-sky-500/30 flex items-center justify-center shrink-0">
                                <i data-lucide="user-check" class="w-5 h-5 text-sky-400"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-white">2. Dados Pessoais & Credenciais de Acesso</h2>
                                <p class="text-xs text-slate-400">Crie sua senha agora. O acesso ao Portal do Cliente será desbloqueado assim que sua proposta for aprovada.</p>
                            </div>
                        </div>

                        <div class="fields-grid-2">
                            <div class="span-2-sm">
                                <label class="asbank-label" for="name">Nome Completo <span class="req-mark">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                       placeholder="Nome completo conforme documento" class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="document">CPF ou CNPJ <span class="req-mark">*</span></label>
                                <input type="text" name="document" id="document" value="{{ old('document') }}" required
                                       placeholder="000.000.000-00" class="asbank-input font-mono" oninput="maskCpfCnpj(this)">
                            </div>

                            <div>
                                <label class="asbank-label" for="rg">RG (Opcional)</label>
                                <input type="text" name="rg" id="rg" value="{{ old('rg') }}"
                                       placeholder="Número do RG e órgão emissor" class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="birth_date">Data de Nascimento <span class="req-mark">*</span></label>
                                <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}" required class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="phone">Telefone / WhatsApp com DDD <span class="req-mark">*</span></label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                                       placeholder="(11) 99999-9999" class="asbank-input font-mono" oninput="maskPhone(this)">
                                <span class="text-[11px] text-emerald-400/90 mt-1 block">Usaremos este WhatsApp para contato e envio da aprovação.</span>
                            </div>

                            <div class="span-2-sm">
                                <label class="asbank-label" for="email">E-mail de Acesso <span class="req-mark">*</span></label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                       placeholder="seu@email.com" class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="password">Senha de Acesso ao Portal <span class="req-mark">*</span></label>
                                <input type="password" name="password" id="password" required minlength="6"
                                       placeholder="Mínimo de 6 caracteres" class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="password_confirmation">Confirmar Senha <span class="req-mark">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6"
                                       placeholder="Repita a senha criada" class="asbank-input">
                            </div>
                        </div>
                    </section>

                    <!-- 3. Renda e Perfil Profissional -->
                    <section class="glass-section space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-white/10">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center shrink-0">
                                <i data-lucide="briefcase" class="w-5 h-5 text-emerald-400"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-white">3. Renda Mensal e Ocupação</h2>
                                <p class="text-xs text-slate-400">Esses dados ajudam nossa equipe a avaliar a melhor taxa e margem de aprovação.</p>
                            </div>
                        </div>

                        <div class="fields-grid-2">
                            <div>
                                <label class="asbank-label" for="occupation">Profissão / Ocupação Atual <span class="req-mark">*</span></label>
                                <input type="text" name="occupation" id="occupation" value="{{ old('occupation') }}" required
                                       placeholder="Ex: Autônomo, Empresário, CLT, MEI..." class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="monthly_income">Renda Mensal Aproximada (R$) <span class="req-mark">*</span></label>
                                <input type="number" step="0.01" min="0" name="monthly_income" id="monthly_income"
                                       value="{{ old('monthly_income') }}" required placeholder="Ex: 4500.00" class="asbank-input font-semibold">
                            </div>
                        </div>
                    </section>

                    <!-- 4. Endereço Completo -->
                    <section class="glass-section space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-white/10">
                            <div class="w-9 h-9 rounded-xl bg-sky-500/15 border border-sky-500/30 flex items-center justify-center shrink-0">
                                <i data-lucide="map-pin" class="w-5 h-5 text-sky-400"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-white">4. Endereço Completo</h2>
                                <p class="text-xs text-slate-400">Digite o CEP para preenchimento automático do endereço.</p>
                            </div>
                        </div>

                        <div class="fields-grid-3">
                            <div>
                                <label class="asbank-label" for="zip_code">CEP <span class="req-mark">*</span></label>
                                <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code') }}" required
                                       placeholder="00000-000" maxlength="9" class="asbank-input font-mono" oninput="maskCep(this)">
                                <span id="cepFeedback" class="text-[11px] text-slate-400 mt-1 block"></span>
                            </div>

                            <div class="span-2-sm">
                                <label class="asbank-label" for="address">Logradouro / Rua / Avenida <span class="req-mark">*</span></label>
                                <input type="text" name="address" id="address" value="{{ old('address') }}" required
                                       placeholder="Rua, Avenida, Travessa..." class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="number">Número <span class="req-mark">*</span></label>
                                <input type="text" name="number" id="number" value="{{ old('number') }}" required
                                       placeholder="Nº ou S/N" class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="complement">Complemento</label>
                                <input type="text" name="complement" id="complement" value="{{ old('complement') }}"
                                       placeholder="Apto, Bloco, Casa..." class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="neighborhood">Bairro <span class="req-mark">*</span></label>
                                <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood') }}" required
                                       placeholder="Bairro" class="asbank-input">
                            </div>

                            <div class="span-2-sm">
                                <label class="asbank-label" for="city">Cidade <span class="req-mark">*</span></label>
                                <input type="text" name="city" id="city" value="{{ old('city') }}" required
                                       placeholder="Cidade" class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="state">UF <span class="req-mark">*</span></label>
                                <input type="text" name="state" id="state" value="{{ old('state') }}" required maxlength="2"
                                       placeholder="SP" class="asbank-input uppercase font-mono" oninput="this.value = this.value.toUpperCase()">
                            </div>
                        </div>
                    </section>

                    <!-- 5. Dados Bancários & Chave PIX para Recebimento -->
                    <section class="glass-section space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-white/10">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center shrink-0">
                                <i data-lucide="qr-code" class="w-5 h-5 text-amber-400"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-white">5. Dados Bancários & Chave PIX de Recebimento</h2>
                                <p class="text-xs text-slate-400">Informe a chave PIX de sua titularidade onde o crédito aprovado será transferido.</p>
                            </div>
                        </div>

                        <div class="fields-grid-3">
                            <div>
                                <label class="asbank-label" for="bank_name">Banco / Instituição <span class="req-mark">*</span></label>
                                <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}" required
                                       placeholder="Ex: Nubank, Itaú, Inter, Bradesco..." class="asbank-input">
                            </div>

                            <div>
                                <label class="asbank-label" for="bank_agency">Agência (Opcional)</label>
                                <input type="text" name="bank_agency" id="bank_agency" value="{{ old('bank_agency') }}"
                                       placeholder="Ex: 0001" class="asbank-input font-mono">
                            </div>

                            <div>
                                <label class="asbank-label" for="bank_account">Conta com Dígito (Opcional)</label>
                                <input type="text" name="bank_account" id="bank_account" value="{{ old('bank_account') }}"
                                       placeholder="Ex: 123456-7" class="asbank-input font-mono">
                            </div>
                        </div>

                        <div class="fields-grid-2">
                            <div>
                                <label class="asbank-label" for="pix_key_type">Tipo de Chave PIX <span class="req-mark">*</span></label>
                                <select name="pix_key_type" id="pix_key_type" required class="asbank-input">
                                    @php $oldPixType = old('pix_key_type', 'cpf'); @endphp
                                    <option value="cpf" {{ $oldPixType === 'cpf' ? 'selected' : '' }}>CPF</option>
                                    <option value="cnpj" {{ $oldPixType === 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                                    <option value="phone" {{ $oldPixType === 'phone' ? 'selected' : '' }}>Telefone / Celular</option>
                                    <option value="email" {{ $oldPixType === 'email' ? 'selected' : '' }}>E-mail</option>
                                    <option value="random" {{ $oldPixType === 'random' ? 'selected' : '' }}>Chave Aleatória (EVP)</option>
                                </select>
                            </div>

                            <div>
                                <label class="asbank-label" for="pix_key">Chave PIX <span class="req-mark">*</span></label>
                                <input type="text" name="pix_key" id="pix_key" value="{{ old('pix_key') }}" required
                                       placeholder="Digite exatamente sua chave PIX" class="asbank-input font-mono">
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <button type="button" class="quick-pill" onclick="copyFieldToPix('document', 'cpf')">Usar CPF/CNPJ</button>
                                    <button type="button" class="quick-pill" onclick="copyFieldToPix('phone', 'phone')">Usar WhatsApp</button>
                                    <button type="button" class="quick-pill" onclick="copyFieldToPix('email', 'email')">Usar E-mail</button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- 6. Termos e Envio -->
                    <section class="glass-section space-y-5">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="accept_terms" value="1" {{ old('accept_terms') ? 'checked' : '' }} required
                                   class="mt-1 rounded bg-[#070D18] border-[#1E3252] text-sky-500 focus:ring-sky-500">
                            <span class="text-xs text-slate-300 leading-relaxed">
                                Declaro que as informações fornecidas são verdadeiras e autorizo a equipe <strong>ASBANK</strong> a realizar a análise cadastral e de crédito, bem como entrar em contato pelo WhatsApp informado para envio de proposta, contraproposta ou confirmação da aprovação. Estou ciente de que meu acesso ao Portal permanecerá pendente até a aprovação da proposta.
                            </span>
                        </label>

                        <button type="submit"
                                class="w-full py-4 px-6 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-black text-sm uppercase tracking-wider flex items-center justify-center gap-2.5 shadow-xl transition cursor-pointer">
                            <i data-lucide="send" class="w-5 h-5"></i>
                            <span>Enviar Proposta para Análise</span>
                        </button>
                    </section>

                </div>

                <!-- RIGHT COLUMN: Sticky Simulation Summary -->
                <aside class="apply-sidebar space-y-4">
                    <div class="glass-section space-y-5" style="border-color: rgba(245, 158, 11, 0.28);">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-sky-400">Resumo da Simulação</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                2,50% a.m.
                            </span>
                        </div>

                        <div class="p-4 rounded-xl bg-[#070D18] border border-[#1E3252] text-center space-y-1">
                            <span class="text-xs text-slate-400">Parcela Mensal Estimada</span>
                            <div id="sumParcelText" class="text-2xl sm:text-3xl font-black text-amber-400">
                                {{ $installmentsCount }}x de R$ {{ number_format($installmentValue, 2, ',', '.') }}
                            </div>
                            <span class="text-[11px] text-slate-500 block">1º vencimento estimado para 30 dias</span>
                        </div>

                        <div>
                            <div class="summary-line">
                                <span class="text-slate-400">Valor Solicitado (PIX)</span>
                                <strong id="sumAmount" class="text-white">R$ {{ number_format($amount, 2, ',', '.') }}</strong>
                            </div>
                            <div class="summary-line">
                                <span class="text-slate-400">Encargos do Período</span>
                                <strong id="sumInterest" class="text-amber-300">+ R$ {{ number_format($totalInterest, 2, ',', '.') }}</strong>
                            </div>
                            <div class="summary-line">
                                <span class="text-slate-300 font-semibold">Total Estimado</span>
                                <strong id="sumTotal" class="text-sky-400 text-base">R$ {{ number_format($totalAmount, 2, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="p-3.5 rounded-xl bg-sky-500/10 border border-sky-500/20 text-xs text-slate-300 space-y-1.5">
                            <div class="flex items-center gap-2 font-bold text-sky-300">
                                <i data-lucide="shield-check" class="w-4 h-4"></i> Como funciona após o envio?
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                1. Sua conta é criada com status <strong class="text-amber-300">Pendente</strong>.<br>
                                2. Nossa equipe analisa seus dados e pode ajustar condições com você via <strong class="text-emerald-400">WhatsApp</strong>.<br>
                                3. Após aprovação, seu acesso é liberado e o PIX é enviado para sua chave.
                            </p>
                        </div>
                    </div>
                </aside>
            </form>
        </div>
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

        function formatBRL(val) {
            return val.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        function recalcProposalSummary() {
            const amount = Math.max(0, parseFloat(document.getElementById('inputAmount').value) || 0);
            const months = Math.max(1, Math.min(48, parseInt(document.getElementById('inputInstallments').value) || 1));
            const rate = 0.025;
            const interest = amount * rate * months;
            const total = amount + interest;
            const parcel = total / months;

            document.getElementById('sumParcelText').innerText = months + 'x de ' + formatBRL(parcel);
            document.getElementById('sumAmount').innerText = formatBRL(amount);
            document.getElementById('sumInterest').innerText = '+ ' + formatBRL(interest);
            document.getElementById('sumTotal').innerText = formatBRL(total);
        }

        function maskCpfCnpj(el) {
            let v = el.value.replace(/\D/g, '').slice(0, 14);
            if (v.length <= 11) {
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
                v = v.replace(/^(\d{2})(\d)/, '$1.$2');
                v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
                v = v.replace(/(\d{4})(\d)/, '$1-$2');
            }
            el.value = v;
        }

        function maskPhone(el) {
            let v = el.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (v.length > 6) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            }
            el.value = v;
        }

        function maskCep(el) {
            let digits = el.value.replace(/\D/g, '').slice(0, 8);
            el.value = digits.length > 5 ? digits.slice(0, 5) + '-' + digits.slice(5) : digits;

            if (digits.length === 8) {
                const fb = document.getElementById('cepFeedback');
                fb.innerText = 'Buscando endereço...';
                fetch('https://viacep.com.br/ws/' + digits + '/json/')
                    .then(r => r.json())
                    .then(data => {
                        if (!data.erro) {
                            if (data.logradouro) document.getElementById('address').value = data.logradouro;
                            if (data.bairro) document.getElementById('neighborhood').value = data.bairro;
                            if (data.localidade) document.getElementById('city').value = data.localidade;
                            if (data.uf) document.getElementById('state').value = data.uf.toUpperCase();
                            fb.innerText = 'Endereço preenchido automaticamente.';
                            fb.className = 'text-[11px] text-emerald-400 mt-1 block';
                        } else {
                            fb.innerText = 'CEP não encontrado, preencha manualmente.';
                            fb.className = 'text-[11px] text-amber-400 mt-1 block';
                        }
                    })
                    .catch(() => {
                        fb.innerText = '';
                    });
            }
        }

        function copyFieldToPix(fieldId, keyType) {
            const src = document.getElementById(fieldId);
            if (!src || !src.value.trim()) {
                src?.focus();
                return;
            }
            let val = src.value.trim();
            const digits = val.replace(/\D/g, '');
            if (keyType === 'cpf') {
                document.getElementById('pix_key_type').value = digits.length > 11 ? 'cnpj' : 'cpf';
                val = digits;
            } else if (keyType === 'phone') {
                document.getElementById('pix_key_type').value = 'phone';
                val = digits;
            } else {
                document.getElementById('pix_key_type').value = 'email';
            }
            document.getElementById('pix_key').value = val;
        }
    </script>
</body>
</html>
