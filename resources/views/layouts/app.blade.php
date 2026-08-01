<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1f5e3b">

    <title>{{ $title ?? config('app.name') }}</title>

    <x-ui.pwa-meta />

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-avicore-surface text-avicore-text antialiased">
        {{ $slot }}

        <x-ui.pwa-install-prompt />

        @livewireScripts
    </body>
</html>
