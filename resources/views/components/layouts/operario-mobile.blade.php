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
<body class="min-h-screen bg-avicore-surface pb-safe">
    <header class="sticky top-0 z-10 border-b border-gray-200 bg-avicore-primary px-4 py-3 text-white shadow-sm">
        <div class="mx-auto flex max-w-lg items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-white/80">Galpón actual</p>
                <p class="text-base font-semibold">{{ $galpon ?? 'Sin seleccionar' }}</p>
            </div>
            <button type="button" class="rounded-lg bg-white/15 px-3 py-2 text-sm font-medium hover:bg-white/25">
                Cambiar
            </button>
        </div>
    </header>

    <main class="mx-auto max-w-lg px-4 py-4">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
