@php
    use App\Support\AdminNav;
@endphp

<x-ui.tab-bar
    aria-label="Navegación panel"
    :tabs="AdminNav::tabBarItems()"
/>
