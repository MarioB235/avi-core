<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-avicore-surface">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white lg:block">
            <div class="border-b border-gray-200 px-6 py-5">
                <p class="text-lg font-semibold text-avicore-primary">AviCore</p>
                <p class="text-sm text-avicore-muted">Panel administrativo</p>
            </div>
            <nav class="space-y-1 px-3 py-4 text-sm">
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 text-avicore-muted hover:bg-avicore-soft hover:text-avicore-primary">Inicio</a>
                <span class="block rounded-lg px-3 py-2 text-avicore-muted">Dashboard (próximo)</span>
                <span class="block rounded-lg px-3 py-2 text-avicore-muted">Galpones (próximo)</span>
            </nav>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-semibold text-avicore-text">{{ $heading ?? 'Panel' }}</h1>
                        @isset($subheading)
                            <p class="text-sm text-avicore-muted">{{ $subheading }}</p>
                        @endisset
                    </div>
                    <div class="text-sm text-avicore-muted">Empresa demo</div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
