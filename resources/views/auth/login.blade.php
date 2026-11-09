<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="seu@email.com"
                   class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-400" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Senha</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full px-4 py-2.5 rounded-xl bg-[#070D18] border border-[#162540] text-sm text-white focus:border-sky-500">
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-400" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center text-xs">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded text-sky-600 bg-slate-900 border-slate-700">
                <span class="text-slate-400">Lembrar-me</span>
            </label>
        </div>

        <div>
            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-500 hover:to-blue-600 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-sky-900/30 transition-all flex items-center justify-center gap-2">
                <i data-lucide="log-in" class="w-4 h-4"></i> Entrar no Sistema
            </button>
        </div>
    </form>
</x-guest-layout>
