@extends('layouts.admin')

@section('header-title', 'Editar Contrato ' . $loan->contract_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.loans.show', $loan) }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar aos detalhes do empréstimo
        </a>
    </div>

    <form method="POST" action="{{ route('admin.loans.update', $loan) }}" class="p-8 rounded-2xl bg-[#0A1324] border border-[#162540] space-y-6">
        @csrf
        @method('PUT')

        <div>
            <h2 class="text-base font-bold text-white flex items-center gap-2 mb-1">
                <i data-lucide="pencil" class="w-4 h-4 text-sky-400"></i> Atualizar Status e Informações do Contrato
            </h2>
            <p class="text-xs text-slate-400">Mutuário: <strong>{{ $loan->client->name }}</strong> • Valor: <strong>R$ {{ number_format($loan->amount, 2, ',', '.') }}</strong></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Status Geral do Contrato</label>
                <select name="status" class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
                    <option value="em_analise" {{ old('status', $loan->status) === 'em_analise' ? 'selected' : '' }}>Em Análise</option>
                    <option value="aprovado" {{ old('status', $loan->status) === 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                    <option value="ativo" {{ old('status', $loan->status) === 'ativo' ? 'selected' : '' }}>Ativo / Em Andamento</option>
                    <option value="quitado" {{ old('status', $loan->status) === 'quitado' ? 'selected' : '' }}>Quitado</option>
                    <option value="rejeitado" {{ old('status', $loan->status) === 'rejeitado' ? 'selected' : '' }}>Rejeitado</option>
                    <option value="cancelado" {{ old('status', $loan->status) === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Score de Análise de Crédito</label>
                <input type="number" min="300" max="1000" name="credit_score" value="{{ old('credit_score', $loan->credit_score) }}" 
                       class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 font-mono">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Chave PIX de Desembolso</label>
                <input type="text" name="pix_key" value="{{ old('pix_key', $loan->pix_key) }}" 
                       class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 font-mono">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Parecer e Notas de Crédito</label>
                <textarea name="credit_analysis_notes" rows="3" class="w-full px-4 py-2 rounded-xl bg-[#070D18] border border-[#162540] text-xs text-white focus:border-sky-500">{{ old('credit_analysis_notes', $loan->credit_analysis_notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#162540]">
            <a href="{{ route('admin.loans.show', $loan) }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-white transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-sm shadow-lg shadow-sky-900/30 transition-all flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
