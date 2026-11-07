@extends('layouts.client')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('client.loans.show', $loan) }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Voltar para detalhes do contrato
        </a>
        <a href="{{ route('admin.loans.contract-print', $loan) }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition-colors flex items-center gap-1.5 shadow">
            <i data-lucide="printer" class="w-4 h-4"></i> Imprimir Contrato
        </a>
    </div>

    <!-- Contract Digital Document -->
    <div class="p-8 rounded-3xl bg-[#0A1324] border border-[#162540] shadow-2xl space-y-6">
        <div class="flex items-center justify-between pb-6 border-b border-[#162540]">
            <div>
                <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK" class="h-10 w-auto rounded-lg shadow-md mb-2">
                <p class="text-xs text-slate-400">Instrumento Particular de Crédito Mútuo Digital</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block">Número do Contrato</span>
                <span class="text-lg font-black text-white font-mono">{{ $loan->contract_number }}</span>
            </div>
        </div>

        <!-- Credit Analysis Report Box -->
        <div class="p-5 rounded-2xl bg-[#070D18] border border-amber-500/20 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i> Relatório de Análise e Score de Crédito
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Score: {{ $loan->credit_score }} / 1000
                </span>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                {{ $loan->credit_analysis_notes ?: 'Contrato aprovado com base na política de crédito automatizada ASBANK e verificação cadastral antifraude.' }}
            </p>
        </div>

        <!-- Contract Clauses -->
        <div class="p-6 rounded-2xl bg-[#070D18] border border-[#162540] space-y-4">
            {!! $loan->contract_terms !!}
        </div>
    </div>

</div>
@endsection
