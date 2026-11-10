@extends('layouts.admin')

@section('header-title', 'Gerenciador de Clientes')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Search Bar -->
    <div class="p-6 rounded-2xl bg-[#0A1324] border border-[#162540] flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.clients.index') }}" class="flex-1 flex flex-col sm:flex-row items-center gap-3 w-full">
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nome, CPF/CNPJ, e-mail ou telefone..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
            </div>

            <select name="status" class="w-full sm:w-44 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-slate-300 focus:border-sky-500">
                <option value="">Todos os status</option>
                <option value="ativo" {{ $status === 'ativo' ? 'selected' : '' }}>Ativos</option>
                <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>Em Análise (Pendentes)</option>
                <option value="rejeitado" {{ $status === 'rejeitado' ? 'selected' : '' }}>Rejeitados</option>
                <option value="inativo" {{ $status === 'inativo' ? 'selected' : '' }}>Inativos</option>
                <option value="bloqueado" {{ $status === 'bloqueado' ? 'selected' : '' }}>Bloqueados</option>
            </select>

            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-medium text-sm transition-colors">
                Filtrar
            </button>
            @if($search || $status)
                <a href="{{ route('admin.clients.index') }}" class="text-xs text-slate-400 hover:text-white px-2">Limpar</a>
            @endif
        </form>

        <a href="{{ route('admin.clients.create') }}" class="w-full md:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-sm shadow-lg shadow-sky-900/30 flex items-center justify-center gap-2 whitespace-nowrap">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Cadastrar Cliente</span>
        </a>
    </div>

    <!-- Clients Table / Cards -->
    <div class="rounded-2xl bg-[#0A1324] border border-[#162540] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-[#070D18] text-xs font-semibold uppercase text-slate-400 tracking-wider border-b border-[#162540]">
                    <tr>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4">CPF / CNPJ</th>
                        <th class="px-6 py-4">Chave PIX</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Empréstimos</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#162540]">
                    @forelse($clients as $client)
                    <tr class="hover:bg-[#0D182E] transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-sky-600 to-amber-500/80 flex items-center justify-center font-bold text-white text-xs shadow-md">
                                    {{ substr($client->name, 0, 2) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.clients.show', $client) }}" class="font-bold text-white hover:text-sky-400 transition-colors">
                                        {{ $client->name }}
                                    </a>
                                    <div class="text-xs text-slate-400">{{ $client->email }}</div>
                                    @if($client->user)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-emerald-400">
                                            <i data-lucide="key" class="w-2.5 h-2.5"></i> Portal ativado
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs">
                            {{ $client->formattedDocument() }}
                            <div class="text-[11px] text-slate-400">{{ $client->formattedPhone() }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs text-amber-300 bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-500/20">
                                {{ strtoupper($client->pix_key_type) }}: {{ $client->pix_key }}
                            </span>
                            @if($client->bank_name)
                                <div class="text-[11px] text-slate-400 mt-1">{{ $client->bank_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($client->status === 'ativo')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Ativo</span>
                            @elseif($client->status === 'pendente')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Em Análise</span>
                            @elseif($client->status === 'rejeitado')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">Rejeitado</span>
                            @elseif($client->status === 'inativo')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-400 border border-slate-500/20">Inativo</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">Bloqueado</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-white">{{ $client->loans_count }}</span>
                            <span class="text-xs text-slate-400">contratos</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.loans.create', ['client_id' => $client->id]) }}" 
                                   title="Criar Empréstimo" 
                                   class="p-2 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 transition-colors">
                                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.clients.show', $client) }}" 
                                   title="Visualizar Detalhes" 
                                   class="p-2 rounded-lg bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('admin.clients.edit', $client) }}" 
                                   title="Editar Cliente" 
                                   class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.clients.destroy', $client) }}" 
                                      onsubmit='return confirmAction(event, "Excluir Cliente", "Tem certeza que deseja excluir o cliente {{ $client->name }}? Todos os dados vinculados serão removidos.", "trash-2", "rose");' class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Excluir Cliente" class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            Nenhum cliente encontrado. <a href="{{ route('admin.clients.create') }}" class="text-sky-400 hover:underline">Clique aqui para cadastrar</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clients->hasPages())
        <div class="p-4 border-t border-[#162540]">
            {{ $clients->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

