<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASBANK') }} - Acesso</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <script src="https://unpkg.com/lucide@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#070D18] text-slate-100 min-h-screen flex flex-col justify-center items-center p-4">
    <div class="w-full sm:max-w-md space-y-6">
        <div class="text-center">
            <a href="/" class="inline-block">
                <img src="{{ asset('images/logo.jpg') }}" alt="ASBANK" class="h-14 w-auto rounded-xl shadow-xl mx-auto border border-sky-500/20">
            </a>
            <p class="text-xs text-slate-400 mt-2">ASBANK • Acesso ao Sistema</p>
        </div>

        <div class="p-8 rounded-3xl bg-[#0A1324] border border-[#162540] shadow-2xl">
            {{ $slot }}
        </div>

        <div class="text-center text-xs text-slate-500">
            © {{ date('Y') }} ASBANK • Confiança que cresce.
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
