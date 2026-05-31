<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1f5e3b">

    <title>{{ $title ?? config('app.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-avicore-surface">
    <div class="flex min-h-screen">
        <aside class="hidden w-64 shrink-0 flex-col border-r border-avicore-border bg-avicore-card lg:flex">
            <div class="border-b border-avicore-border px-5 py-5">
                <x-ui.logo />
                <p class="mt-2 text-xs text-avicore-muted">Panel administrativo</p>
            </div>
            <nav class="flex-1 space-y-1 px-3 py-4" aria-label="Navegación principal">
                <x-ui.nav-link :href="route('home')" :active="request()->routeIs('home')">
                    Inicio
                </x-ui.nav-link>
                <x-ui.nav-link disabled>Dashboard (próximo)</x-ui.nav-link>
                <x-ui.nav-link disabled>Galpones (próximo)</x-ui.nav-link>
            </nav>
            <div class="border-t border-avicore-border px-5 py-4">
                <p class="truncate text-xs text-avicore-muted">Sesión</p>
                @auth
                    <p class="truncate text-sm font-medium text-avicore-text">{{ auth()->user()->name }}</p>
                @endauth
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-10 border-b border-avicore-border bg-avicore-card/95 px-4 py-4 shadow-avicore-header backdrop-blur-sm sm:px-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 class="avicore-page-title">{{ $heading ?? 'Panel' }}</h1>
                        @isset($subheading)
                            <p class="avicore-page-subtitle">{{ $subheading }}</p>
                        @endisset
                    </div>
                    <div class="flex items-center gap-3">
                        <x-ui.badge variant="primary">Empresa demo</x-ui.badge>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <div class="mx-auto max-w-6xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
