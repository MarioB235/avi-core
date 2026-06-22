@props([
    'saludo',
    'galponEtiqueta',
    'hasGalpon' => false,
])

<section class="avicore-operario-home-hero" aria-label="Bienvenida">
    <div class="avicore-operario-home-hero__media" aria-hidden="true">
        <img
            src="{{ asset('images/brand/operario-home-hero.jpg') }}"
            alt=""
            class="avicore-operario-home-hero__img"
            width="1080"
            height="1440"
            decoding="async"
        >
        <div class="avicore-operario-home-hero__scrim"></div>
    </div>

    <div class="avicore-operario-home-hero__content">
        <x-operario.header :is-home-page="true" />

        <div class="avicore-operario-home-hero__greeting">
            @php
                $primerNombre = explode(' ', trim(auth()->user()->name))[0] ?? auth()->user()->name;
            @endphp
            <h1 class="avicore-operario-home-hero__title">
                ¡{{ $saludo }}, {{ $primerNombre }}!
            </h1>
            <p class="avicore-operario-home-hero__subtitle">
                Acá tenés el resumen de tu granja.
            </p>

            <a
                href="{{ route('operario.galpon') }}"
                wire:navigate
                @class([
                    'avicore-operario-home-hero__galpon',
                    'avicore-operario-home-hero__galpon--empty' => ! $hasGalpon,
                ])
            >
                <x-ui.icon name="warehouse" class="size-4 shrink-0" />
                <span class="truncate">{{ $galponEtiqueta }}</span>
                <x-ui.icon name="chevron-down" class="size-4 shrink-0 opacity-80" />
            </a>
        </div>
    </div>
</section>
