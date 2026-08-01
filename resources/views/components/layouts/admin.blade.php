<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1f5e3b">

    <title>{{ $title ?? 'Panel · '.config('app.name') }}</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="avicore-operario-body font-sans">
    <div @class([
        'avicore-operario-shell',
        'avicore-operario-shell--home' => $adminIsHeroPage ?? false,
    ])>
        <x-admin.sidebar-nav />

        <div class="avicore-operario-shell__workspace">
            @if ($adminIsHeroPage ?? false)
                <x-admin.header :is-home-page="true" />
            @else
                <div wire:transition="operario-chrome">
                    <x-admin.header
                        :title="$adminHeaderTitle ?? 'Panel'"
                        :badge="$adminRoleBadge ?? null"
                    />
                </div>
            @endif

            <main @class([
                'avicore-operario-main',
                'avicore-operario-main--home' => $adminIsHeroPage ?? false,
            ])>
                <div wire:transition="operario-page" class="avicore-operario-page">
                    {{ $slot }}
                </div>
            </main>

            <x-admin.bottom-nav />
        </div>
    </div>

    <x-ui.snackbar-host context="operario" />

    @livewireScripts
</body>
</html>
