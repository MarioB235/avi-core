@props([
    'seccion' => 'datos',
])

@php
    $copy = match ($seccion) {
        'password' => [
            'title' => 'Contraseña',
            'subtitle' => 'Cambiá tu clave de acceso.',
            'label' => 'Cambiar contraseña',
        ],
        default => [
            'title' => 'Mis datos',
            'subtitle' => 'Actualizá tu nombre y correo de contacto.',
            'label' => 'Mis datos',
        ],
    };
@endphp

<section class="avicore-operario-home-hero avicore-operario-perfil-hero" aria-label="{{ $copy['label'] }}">
    <div class="avicore-operario-home-hero__media" aria-hidden="true"></div>

    <div class="avicore-operario-home-hero__content">
        <div class="avicore-operario-home-hero__greeting">
            <h1 class="avicore-operario-home-hero__title">
                {{ $copy['title'] }}
            </h1>
            <p class="avicore-operario-home-hero__subtitle avicore-operario-perfil-hero__subtitle">
                {{ $copy['subtitle'] }}
            </p>

            <div class="avicore-operario-perfil-hero__chip-spacer" aria-hidden="true"></div>
        </div>
    </div>
</section>
