@extends('layouts.client')

@section('content')
<div class="max-w-xl mx-auto py-12 text-center space-y-6">
    <div class="w-20 h-20 rounded-3xl bg-amber-500/10 border border-amber-500/20 text-amber-400 mx-auto flex items-center justify-center">
        <i data-lucide="user-check" class="w-10 h-10"></i>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-white">Perfil em Vinculação</h1>
        <p class="text-sm text-slate-400 mt-2">
            Sua conta de usuário foi autenticada, mas ainda não localizamos um cadastro de mutuário associado ao seu e-mail ({{ auth()->user()->email }}).
        </p>
    </div>

    <div class="p-4 rounded-2xl bg-[#0A1324] border border-[#162540] text-xs text-slate-400">
        Entre em contato com a equipe ASBANK para que o administrador realize seu cadastro de cliente e libere seus contratos e simulações.
    </div>

    @if(auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs uppercase tracking-wider transition-colors">
            <i data-lucide="shield" class="w-4 h-4"></i> Ir para o Painel do Administrador
        </a>
    @endif
</div>
@endsection
