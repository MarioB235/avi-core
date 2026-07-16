@php
    use App\Support\OperarioNav;

    $tabs = OperarioNav::tabs();
@endphp

<aside class="avicore-operario-sidebar hidden lg:flex" aria-label="Navegación operario escritorio">
    <div class="avicore-operario-sidebar__brand">
        <x-ui.logo subtitle="Carga en campo" theme="on-primary" class="min-w-0 flex-1" />
    </div>

    <p class="avicore-operario-sidebar__section-label">Navegación</p>

    <nav class="avicore-operario-sidebar__nav" aria-label="Secciones operario">
        @foreach ($tabs as $tab)
            @php($active = OperarioNav::tabIsActive($tab))

            <x-ui.nav-link
                :href="route($tab['route'])"
                :icon="$tab['icon']"
                :active="$active"
                wire:navigate.hover
            >
                {{ $tab['label'] }}
            </x-ui.nav-link>
        @endforeach
    </nav>

    <div class="avicore-operario-sidebar__footer">
        <p class="avicore-operario-sidebar__section-label">Cuenta</p>

        @auth
            <div class="avicore-operario-sidebar__user">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-white/70">{{ auth()->user()->rol->label() }}</p>
                </div>

                <x-operario.user-menu
                    size="sm"
                    variant="sidebar"
                    avatar-class="avicore-operario-sidebar__menu-trigger shrink-0"
                />
            </div>
        @endauth
    </div>
</aside>
