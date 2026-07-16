@props([
    'galponEtiqueta',
    'hasGalpon' => false,
])

<section class="avicore-operario-home-hero avicore-operario-cargar-hero" aria-label="Nueva carga">
    <div class="avicore-operario-home-hero__media" aria-hidden="true"></div>

    <div class="avicore-operario-home-hero__content">
        <div class="avicore-operario-home-hero__greeting">
            <h1 class="avicore-operario-home-hero__title">
                Registrar
            </h1>
            <p class="avicore-operario-home-hero__subtitle">
                Tocá el dato que querés cargar.
            </p>

            {{ $galponSelector }}
        </div>
    </div>
</section>
