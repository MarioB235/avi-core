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
<body class="min-h-screen bg-avicore-surface font-sans">
    <div x-data="{ sidebarOpen: false }" x-on:keydown.escape.window="sidebarOpen = false" class="flex min-h-screen">
        <div
            x-show="sidebarOpen"
            x-cloak
            class="avicore-sidebar-backdrop"
            x-on:click="sidebarOpen = false"
            aria-hidden="true"
        ></div>

        <aside
            x-show="sidebarOpen"
            x-cloak
            class="fixed inset-y-0 left-0 z-50 lg:hidden"
            aria-label="Menú de navegación"
        >
            <div class="avicore-sidebar-panel h-full border-r border-avicore-border">
                @include('components.layouts.partials.admin-sidebar-inner')
            </div>
        </aside>

        <aside class="avicore-sidebar-panel hidden lg:flex">
            @include('components.layouts.partials.admin-sidebar-inner')
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="border-b border-avicore-border bg-avicore-card">
                <div class="flex items-center gap-3 px-4 py-4 sm:px-6">
                    <button
                        type="button"
                        class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-avicore-border text-avicore-text hover:bg-avicore-soft lg:hidden"
                        x-on:click="sidebarOpen = true"
                        aria-label="Abrir menú"
                    >
                        <x-ui.icon name="menu" />
                    </button>

                    <div class="min-w-0 flex-1">
                        <h1 class="avicore-page-title truncate">{{ $heading ?? 'Panel' }}</h1>
                        @isset($subheading)
                            <p class="avicore-page-subtitle truncate">{{ $subheading }}</p>
                        @endisset
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <div class="mx-auto max-w-5xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
