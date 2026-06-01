<nav class="flex-1 space-y-0.5 px-3 py-4" aria-label="Navegación principal">
    <x-ui.nav-link :href="route('admin.home')" :active="request()->routeIs('admin.home')">
        Inicio
    </x-ui.nav-link>
    <x-ui.nav-link disabled>Dashboard</x-ui.nav-link>
    <x-ui.nav-link disabled>Galpones</x-ui.nav-link>
</nav>
