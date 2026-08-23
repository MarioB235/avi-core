@php
    use App\Support\OperarioNav;
@endphp

<x-ui.tab-bar
    aria-label="Navegación operario"
    :tabs="OperarioNav::tabBarItems()"
/>
