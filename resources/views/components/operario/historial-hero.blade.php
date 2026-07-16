@props([
    'galponEtiqueta',
    'hasGalpon' => false,
])

<section class="avicore-operario-home-hero avicore-operario-historial-hero" aria-label="Historial">
    <div class="avicore-operario-home-hero__media" aria-hidden="true"></div>

    <div class="avicore-operario-home-hero__content">
        <div class="avicore-operario-home-hero__greeting">
            <h1 class="avicore-operario-home-hero__title">
                Historial
            </h1>
            <p class="avicore-operario-home-hero__subtitle">
                Todo lo que cargaste, del más nuevo al más viejo.
            </p>

            {{ $galponSelector }}
        </div>
    </div>
</section>
