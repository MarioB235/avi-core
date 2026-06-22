@props([
    'galponEtiqueta',
    'hasGalpon' => false,
])

<section class="avicore-operario-home-hero avicore-operario-cargar-hero" aria-label="Nueva carga">
    <div class="avicore-operario-home-hero__media" aria-hidden="true">
        <img
            src="{{ asset('images/brand/operario-cargar-hero.jpg') }}"
            alt=""
            class="avicore-operario-home-hero__img"
            width="1080"
            height="1440"
            decoding="async"
        >
    </div>

    <div class="avicore-operario-home-hero__content">
        <x-operario.header :is-home-page="true" photo-overlay />

        <div class="avicore-operario-home-hero__greeting">
            <h1 class="avicore-operario-home-hero__title">
                Nueva carga
            </h1>
            <p class="avicore-operario-home-hero__subtitle">
                Elegí el tipo de registro para continuar.
            </p>

            @if ($hasGalpon)
                <div class="avicore-operario-home-hero__galpon avicore-operario-cargar-hero__galpon">
                    <x-ui.icon name="warehouse" class="size-4 shrink-0" />
                    <span class="truncate">{{ $galponEtiqueta }}</span>
                </div>
            @else
                <a
                    href="{{ route('operario.home', ['abrir_galpon' => 1]) }}"
                    wire:navigate
                    class="avicore-operario-home-hero__galpon avicore-operario-home-hero__galpon--empty avicore-operario-cargar-hero__galpon"
                >
                    <x-ui.icon name="warehouse" class="size-4 shrink-0" />
                    <span class="truncate">Sin seleccionar · Elegí en Inicio</span>
                </a>
            @endif
        </div>
    </div>
</section>
