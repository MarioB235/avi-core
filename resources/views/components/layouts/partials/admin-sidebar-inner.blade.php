<div class="flex h-full min-h-0 flex-col">
    <div class="avicore-admin-sidebar__brand">
        <x-ui.logo subtitle="Gestión operativa avícola" theme="on-primary" class="min-w-0 flex-1" />

        @if ($showDrawerClose ?? false)
            <button
                type="button"
                class="avicore-admin-sidebar__close lg:hidden"
                x-on:click="sidebarOpen = false"
                aria-label="Cerrar menú"
            >
                <x-ui.icon name="close" />
            </button>
        @endif
    </div>

    <p class="avicore-admin-sidebar__section-label avicore-sidebar-label">Navegación</p>

    @include('components.layouts.partials.admin-nav')

    <div class="avicore-admin-sidebar__footer">
        <p class="avicore-admin-sidebar__section-label avicore-sidebar-label">Cuenta</p>

        @auth
            <div class="avicore-sidebar-user mb-3">
                <x-ui.user-avatar
                    :name="auth()->user()->name"
                    size="sm"
                    decorative
                    class="avicore-sidebar-user__avatar"
                />
                <div class="avicore-sidebar-footer-text min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-white/70">{{ auth()->user()->rol->label() }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="avicore-nav-link w-full text-left">
                    <x-ui.icon name="logout" class="size-5 shrink-0" />
                    <span class="avicore-sidebar-label">Cerrar sesión</span>
                </button>
            </form>

            <button
                type="button"
                class="avicore-nav-link mt-1 hidden w-full text-left lg:inline-flex"
                x-on:click="sidebarCollapsed = ! sidebarCollapsed"
                :aria-expanded="(! sidebarCollapsed).toString()"
            >
                <x-ui.icon name="panel-left" class="size-5 shrink-0" />
                <span class="avicore-sidebar-label">Colapsar menú</span>
            </button>
        @endauth
    </div>
</div>
