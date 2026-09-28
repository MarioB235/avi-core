@props([
    'seccion',
    'profileRouteBase',
])

<nav class="avicore-operario-perfil__tabs" aria-label="Secciones del perfil" role="tablist">
    <a
        href="{{ $profileRouteBase }}"
        wire:navigate
        role="tab"
        id="perfil-tab-datos"
        aria-controls="perfil-panel-datos"
        aria-selected="{{ $seccion === 'datos' ? 'true' : 'false' }}"
        @class([
            'avicore-operario-perfil__tab',
            'avicore-operario-perfil__tab--active' => $seccion === 'datos',
        ])
    >
        Mis datos
    </a>
    <a
        href="{{ $profileRouteBase }}?seccion=password"
        wire:navigate
        role="tab"
        id="perfil-tab-password"
        aria-controls="perfil-panel-password"
        aria-selected="{{ $seccion === 'password' ? 'true' : 'false' }}"
        @class([
            'avicore-operario-perfil__tab',
            'avicore-operario-perfil__tab--active' => $seccion === 'password',
        ])
    >
        Contraseña
    </a>
    <a
        href="{{ $profileRouteBase }}?seccion=ayuda"
        wire:navigate
        role="tab"
        id="perfil-tab-ayuda"
        aria-controls="perfil-panel-ayuda"
        aria-selected="{{ $seccion === 'ayuda' ? 'true' : 'false' }}"
        @class([
            'avicore-operario-perfil__tab',
            'avicore-operario-perfil__tab--active' => $seccion === 'ayuda',
        ])
    >
        Ayuda
    </a>
</nav>
