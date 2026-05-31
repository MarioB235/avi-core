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
<body class="min-h-screen bg-avicore-surface pb-8">
    <header class="sticky top-0 z-10 border-b border-avicore-primary/20 bg-avicore-primary px-4 py-3 text-white shadow-sm">
        <div class="mx-auto flex max-w-lg items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-medium uppercase tracking-wide text-white/80">Galpón actual</p>
                <p class="truncate text-base font-semibold">{{ $galpon ?? 'Sin seleccionar' }}</p>
            </div>
            <button
                type="button"
                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-lg bg-white/15 px-4 text-sm font-medium transition hover:bg-white/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white active:bg-white/30"
            >
                Cambiar
            </button>
        </div>
    </header>

    <main class="mx-auto max-w-lg space-y-4 px-4 py-5">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
