@extends('layouts.admin')

@section('header-title', 'Editar Cliente')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.clients.show', $client) }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar aos detalhes do cliente
        </a>
    </div>

    <form method="POST" action="{{ route('admin.clients.update', $client) }}" class="p-8 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-8">
        @csrf
        @method('PUT')

        <!-- Dados Pessoais -->
        <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2 mb-4 pb-2 border-b border-[#162540]">
                <i data-lucide="user" class="w-4 h-4 text-sky-400"></i> Informações Principais
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Nome Completo *</label>
                    <input type="text" name="name" value="{{ old('name', $client->name) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                    @error('name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">E-mail *</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                    @error('email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">CPF ou CNPJ *</label>
                    <input type="text" name="document" value="{{ old('document', $client->document) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                    @error('document') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Telefone / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Status Cadastral</label>
                    <select name="status" class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                        <option value="ativo" {{ old('status', $client->status) === 'ativo' ? 'selected' : '' }}>Ativo</option>
                        <option value="inativo" {{ old('status', $client->status) === 'inativo' ? 'selected' : '' }}>Inativo</option>
                        <option value="bloqueado" {{ old('status', $client->status) === 'bloqueado' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Limite de Crédito Disponível (R$)</label>
                    <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $client->credit_limit) }}" 
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
                        <option value="cpf" {{ old('pix_key_type', $client->pix_key_type) === 'cpf' ? 'selected' : '' }}>CPF</option>
                        <option value="cnpj" {{ old('pix_key_type', $client->pix_key_type) === 'cnpj' ? 'selected' : '' }}>CNPJ</option>
                        <option value="email" {{ old('pix_key_type', $client->pix_key_type) === 'email' ? 'selected' : '' }}>E-mail</option>
                        <option value="phone" {{ old('pix_key_type', $client->pix_key_type) === 'phone' ? 'selected' : '' }}>Telefone</option>
                        <option value="random" {{ old('pix_key_type', $client->pix_key_type) === 'random' ? 'selected' : '' }}>Chave Aleatória (EVP)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Chave PIX do Cliente *</label>
                    <input type="text" name="pix_key" value="{{ old('pix_key', $client->pix_key) }}" required 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 font-mono">
                    @error('pix_key') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Banco</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $client->bank_name) }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Agência</label>
                    <input type="text" name="bank_agency" value="{{ old('bank_agency', $client->bank_agency) }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Conta Corrente</label>
                    <input type="text" name="bank_account" value="{{ old('bank_account', $client->bank_account) }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                </div>
            </div>
        </div>

        <!-- Acesso ao Portal & Redefinição de Senha -->
        <div class="p-5 rounded-2xl bg-[#070D18] border border-[#162540]">
            <h2 class="text-base font-bold text-white flex items-center gap-2 mb-2">
                <i data-lucide="key" class="w-4 h-4 text-emerald-400"></i> Acesso ao Portal do Cliente
            </h2>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-300">
                        Status do Usuário: <span class="font-bold {{ $client->user ? 'text-emerald-400' : 'text-amber-400' }}">{{ $client->user ? 'Conta de Acesso Vinculada' : 'Nenhuma conta vinculada' }}</span>
                    </p>
                    <p class="text-[11px] text-slate-400">Defina uma nova senha para resetar o acesso do cliente se necessário.</p>
                </div>
                <div class="w-full sm:w-64">
                    <input type="text" name="new_password" placeholder="Nova senha (opcional)" 
                           class="w-full px-4 py-2 rounded-xl bg-[#0A1324] border border-[#162540] text-xs text-white">
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#162540]">
            <a href="{{ route('admin.clients.show', $client) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-sm shadow-lg shadow-sky-900/30 transition-all flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Atualizar Cliente
            </button>
        </div>

    </form>
</div>
@endsection

