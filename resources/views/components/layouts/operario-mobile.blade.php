<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1f5e3b">

    <title>{{ $title ?? 'Operario · '.config('app.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="avicore-brand-background pb-[max(2rem,env(safe-area-inset-bottom))] font-sans">
    <div class="relative z-10 min-h-screen">
        <header class="border-b border-avicore-border bg-avicore-card/95 px-4 py-3">
            <div class="mx-auto max-w-lg">
                <p class="text-xs text-avicore-muted">Galpón</p>
                <p class="truncate text-base font-medium text-avicore-text">{{ $galpon ?? 'Sin seleccionar' }}</p>
            </div>
        </header>

        <main class="mx-auto w-full max-w-lg space-y-4 px-4 py-5">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
