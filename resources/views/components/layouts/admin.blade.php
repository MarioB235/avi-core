@props([
    'title' => null,
    'heading' => 'Panel',
    'subheading' => null,
    'showDate' => false,
    'contentWide' => false,
    'masthead' => false,
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
<body class="bg-avicore-surface font-sans antialiased">
    <div
        x-data="{ sidebarOpen: false, sidebarCollapsed: false }"
        x-on:keydown.escape.window="sidebarOpen = false"
        class="avicore-admin-shell"
    >
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="avicore-sidebar-backdrop"
            x-on:click="sidebarOpen = false"
            aria-hidden="true"
        ></div>

        <aside
            x-show="sidebarOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="avicore-admin-sidebar avicore-admin-sidebar--drawer"
            aria-label="Menú de navegación"
        >
            @include('components.layouts.partials.admin-sidebar-inner', ['showDrawerClose' => true])
        </aside>

        <aside
            class="avicore-admin-sidebar hidden lg:flex"
            :class="sidebarCollapsed ? 'avicore-sidebar-panel--collapsed' : ''"
            aria-label="Menú de navegación"
        >
            @include('components.layouts.partials.admin-sidebar-inner')
        </aside>

        <div class="avicore-admin-main">
            @if ($masthead)
                @include('components.layouts.partials.admin-header-toolbar', [
                    'heading' => $heading,
                    'subheading' => $subheading,
                    'showDate' => $showDate,
                    'contentWide' => $contentWide,
                ])

                <main class="avicore-admin-main__content" role="main">
                    <div @class([
                        'avicore-admin-main__inner',
                        'avicore-admin-main__inner--wide' => $contentWide ?? false,
                        'avicore-admin-main__inner--with-masthead' => true,
                    ])>
                        {{ $hero ?? '' }}
                        {{ $slot }}
                    </div>
                </main>
            @else
                <header class="avicore-admin-header">
                    <div @class([
                        'avicore-admin-header__inner',
                        'avicore-admin-header__inner--wide' => $contentWide ?? false,
                    ])>
                        @include('components.layouts.partials.admin-menu-trigger')

                        <div class="min-w-0 flex-1">
                            <h1 class="avicore-page-title truncate">{{ $heading ?? 'Panel' }}</h1>
                            @isset($subheading)
                                <p class="avicore-page-subtitle truncate">{{ $subheading }}</p>
                            @endisset
                        </div>

                        @if ($showDate ?? false)
                            <time datetime="{{ now()->toDateString() }}" class="avicore-date-pill">
                                <x-ui.icon name="calendar" class="size-4 shrink-0" />
                                <span class="capitalize">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
                            </time>
                        @endif
                    </div>
                </header>

                <main class="avicore-admin-main__content" role="main">
                    <div @class([
                        'avicore-admin-main__inner',
                        'avicore-admin-main__inner--wide' => $contentWide ?? false,
                    ])>
                        {{ $slot }}
                    </div>
                </main>
            @endif
        </div>
    </div>

    @livewireScripts
</body>
</html>
