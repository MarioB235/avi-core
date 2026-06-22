@props([
    'saludo',
    'primerNombre',
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
            <h1 class="avicore-operario-home-hero__title">
                ¡{{ $saludo }}, {{ $primerNombre }}!
            </h1>
            <p class="avicore-operario-home-hero__subtitle">
                Acá tenés el resumen de tu granja.
            </p>

            {{ $galponSelector }}
        </div>
    </div>
</section>
