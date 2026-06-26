@props([
    'saludo',
])

<section class="avicore-operario-home-hero" aria-label="Bienvenida">
    <div class="avicore-operario-home-hero__media" aria-hidden="true"></div>

    <div class="avicore-operario-home-hero__content">
        <div class="avicore-operario-home-hero__greeting">
            <h1 class="avicore-operario-home-hero__title">
                ¡{{ $saludo }}!
            </h1>
            <p class="avicore-operario-home-hero__subtitle">
                Estado de hoy del galpón.
            </p>

            {{ $galponSelector }}
        </div>
    </div>
</section>
