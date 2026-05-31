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
    <div class="pointer-events-none fixed inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--color-avicore-soft)_0%,_transparent_55%)]" aria-hidden="true"></div>

    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:py-14">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>

    <footer class="pb-6 text-center text-xs text-avicore-muted">
        {{ config('app.name') }} — gestión operativa avícola
    </footer>

    @livewireScripts
</body>
</html>
