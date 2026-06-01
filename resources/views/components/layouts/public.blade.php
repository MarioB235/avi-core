@props([
    'title' => null,
])

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
<body class="avicore-brand-background font-sans">
    <div class="avicore-auth-shell">
        <aside class="avicore-auth-brand hidden lg:block">
            @include('components.layouts.partials.auth-brand-panel')
        </aside>

        <main class="avicore-auth-main">
            <div class="avicore-auth-form-wrap">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
