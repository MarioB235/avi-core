<nav class="avicore-admin-nav" aria-label="Navegación principal">
    <x-ui.nav-link :href="route('admin.home')" :active="request()->routeIs('admin.home')" icon="home">
        Inicio
    </x-ui.nav-link>

    <x-ui.nav-link disabled icon="chart" badge="Próximamente">
        Dashboard
    </x-ui.nav-link>

    <x-ui.nav-link disabled icon="layers">
        Estructura
    </x-ui.nav-link>

    <x-ui.nav-link disabled icon="users">
        Usuarios
    </x-ui.nav-link>

    <x-ui.nav-link disabled icon="file-bar-chart">
        Reportes
    </x-ui.nav-link>

    <x-ui.nav-link disabled icon="clipboard-list">
        Auditoría
    </x-ui.nav-link>

    <x-ui.nav-link disabled icon="bell" badge="Próximamente">
        Notificaciones
    </x-ui.nav-link>
</nav>
