@props([
    'saludo',
    'primerNombre',
])

<section class="avicore-operario-home-hero" aria-label="Bienvenida">
    <div class="avicore-operario-home-hero__media" aria-hidden="true"></div>

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
