@extends('layouts.admin')

@section('header-title', 'Cadastrar Novo Cliente')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.clients.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar para lista de clientes
        </a>
    </div>

    <form method="POST" action="{{ route('admin.clients.store') }}" class="p-8 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-8">
        @csrf

        <!-- Dados Pessoais -->
        <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2 mb-4 pb-2 border-b border-[#162540]">
                <i data-lucide="user" class="w-4 h-4 text-sky-400"></i> Informações Principais
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nome Completo *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Ex: João Carlos da Silva">
                    @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">E-mail *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="cliente@email.com">
                    @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">CPF ou CNPJ *</label>
                    <input type="text" name="document" value="{{ old('document') }}" required 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="000.000.000-00">
                    @error('document') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Telefone / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="(11) 90000-0000">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Data de Nascimento</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Renda Mensal Declarada (R$)</label>
                    <input type="number" step="0.01" name="monthly_income" value="{{ old('monthly_income', '5000.00') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                </div>
            </div>
        </div>

        <!-- Dados Bancários & Chave PIX -->
        <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2 mb-4 pb-2 border-b border-[#162540]">
                <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i> Dados para Envio do PIX (Desembolso MisticPay)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tipo de Chave PIX *</label>
                    <select name="pix_key_type" required class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                        <option value="cpf" {{ old('pix_key_type') === 'cpf' ? 'selected' : '' }}>CPF</option>
                        <option value="cnpj" {{ old('pix_key_type') === 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                        <option value="email" {{ old('pix_key_type') === 'email' ? 'selected' : '' }}>E-mail</option>
                        <option value="phone" {{ old('pix_key_type') === 'phone' ? 'selected' : '' }}>Telefone</option>
                        <option value="random" {{ old('pix_key_type') === 'random' ? 'selected' : '' }}>Chave Aleatória (EVP)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Chave PIX do Cliente *</label>
                    <input type="text" name="pix_key" value="{{ old('pix_key') }}" required 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 font-mono" placeholder="Informe a chave exata para receber o empréstimo">
                    @error('pix_key') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Banco / Instituição</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="Ex: Nubank, Inter, Bradesco">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Agência</label>
                    <input type="text" name="bank_agency" value="{{ old('bank_agency') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="0001">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Conta Corrente</label>
                    <input type="text" name="bank_account" value="{{ old('bank_account') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="123456-7">
                </div>
            </div>
        </div>

        <!-- Endereço -->
        <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2 mb-4 pb-2 border-b border-[#162540]">
                <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400"></i> Localização & Endereço
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">CEP</label>
                    <input type="text" name="zip_code" value="{{ old('zip_code') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="00000-000">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Logradouro (Rua, Av.)</label>
                    <input type="text" name="address" value="{{ old('address') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="Avenida Brasil">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Número</label>
                    <input type="text" name="number" value="{{ old('number') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="123">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Complemento</label>
                    <input type="text" name="complement" value="{{ old('complement') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="Apto 101">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Bairro</label>
                    <input type="text" name="neighborhood" value="{{ old('neighborhood') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="Centro">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Cidade</label>
                    <input type="text" name="city" value="{{ old('city') }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="São Paulo">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Estado (UF)</label>
                    <input type="text" name="state" value="{{ old('state') }}" maxlength="2" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white uppercase focus:border-sky-500" placeholder="SP">
                </div>
            </div>
        </div>

        <!-- Acesso ao Portal do Cliente -->
        <div class="p-5 rounded-2xl bg-[#070D18] border border-[#162540]">
            <h2 class="text-base font-bold text-white flex items-center gap-2 mb-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i> Acesso ao Painel do Cliente
            </h2>
            <p class="text-xs text-slate-400 mb-4">
                Permita que o cliente faça login com seu e-mail para acompanhar contratos, análise de crédito, extrato e gerar o QR Code PIX para pagamento das parcelas.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="create_user_account" value="1" checked class="w-4 h-4 rounded text-sky-600 bg-slate-900 border-slate-700">
                    <span class="text-xs font-semibold text-slate-300">Criar credencial de acesso ao Portal do Cliente</span>
                </label>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Senha Provisória (Opcional)</label>
                    <input type="text" name="password" placeholder="Padrão: asbank123" 
                           class="w-full px-4 py-2 rounded-xl bg-[#0A1324] border border-[#162540] text-xs text-white">
                </div>
            </div>
        </div>

        <!-- Observações -->
        <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Observações Internas</label>
            <textarea name="notes" rows="3" class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500" placeholder="Anotações de crédito ou perfil do cliente...">{{ old('notes') }}</textarea>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#162540]">
            <a href="{{ route('admin.clients.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-sm shadow-lg shadow-sky-900/30 transition-all flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i> Salvar Cliente
            </button>
        </div>

    </form>
</div>
@endsection

