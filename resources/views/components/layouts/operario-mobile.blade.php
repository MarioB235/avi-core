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
<body class="avicore-brand-background font-sans">
    <div class="avicore-operario-shell relative z-10 min-h-screen pb-[calc(5.25rem+env(safe-area-inset-bottom,0px))]">
        <header class="avicore-operario-header">
            <div class="avicore-operario-header__inner">
                <div class="min-w-0 flex-1">
                    <h1 class="avicore-operario-header__title">{{ $operarioHeaderTitle ?? 'Operario' }}</h1>
                    @if (! empty($operarioHeaderSubtitle))
                        <p class="avicore-operario-header__subtitle">{{ $operarioHeaderSubtitle }}</p>
                    @endif
                </div>
            </div>
        </header>

        <main class="avicore-operario-main">
            {{ $slot }}
        </main>

        <x-operario.bottom-nav />
    </div>

    @livewireScripts
</body>
</html>
