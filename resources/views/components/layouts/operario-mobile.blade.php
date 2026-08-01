<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1f5e3b">

    <title>{{ $title ?? 'Operario · '.config('app.name') }}</title>

    <x-ui.pwa-meta />
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="avicore-operario-body font-sans">
    <div @class([
        'avicore-operario-shell',
        'avicore-operario-shell--home' => $operarioIsHeroPage ?? false,
    ])>
        <x-operario.sidebar-nav />

        <div class="avicore-operario-shell__workspace">
            @if ($operarioIsHeroPage ?? false)
                <x-operario.header :is-home-page="true" />
            @else
                <div wire:transition="operario-chrome">
                    <x-operario.header
                        :title="$operarioHeaderTitle ?? 'Operario'"
                        :subtitle="$operarioHeaderSubtitle ?? null"
                        :has-galpon="$operarioHasGalpon ?? false"
                    />
                </div>
            @endif

            <main @class([
                'avicore-operario-main',
                'avicore-operario-main--home' => $operarioIsHeroPage ?? false,
            ])>
                <div wire:transition="operario-page" class="avicore-operario-page">
                    {{ $slot }}
                </div>
            </main>

            <x-operario.bottom-nav />
        </div>
    </div>

    <x-ui.snackbar-host context="operario" />
    <x-ui.pwa-install-prompt />

    @livewireScripts
</body>
</html>
