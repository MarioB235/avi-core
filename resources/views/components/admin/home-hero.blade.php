@props([
    'welcomeTitle' => '¡Bienvenido de nuevo!',
    'welcomeSubtitle' => 'Aquí tienes un resumen general de la operación.',
])

{{-- Hero Inicio admin: tarjeta con foto + copy; KPIs debajo. Móvil: avicore-defer: admin-home-hero-mobile.jpg, cuando exista asset en tokens-componentes.md --}}
<section class="avicore-home-hero">
    <div class="avicore-home-hero__card">
        <figure class="avicore-home-hero__figure" aria-hidden="true">
            <img
                src="{{ asset('images/brand/admin-home-hero.jpg') }}"
                alt=""
                class="avicore-home-hero__img"
                width="1920"
                height="800"
                decoding="async"
            >
            <div class="avicore-home-hero__scrim"></div>
        </figure>

        <div class="avicore-home-hero__copy">
            <h2 class="avicore-home-hero__title">{{ $welcomeTitle }}</h2>
            <p class="avicore-home-hero__subtitle">{{ $welcomeSubtitle }}</p>
        </div>
    </div>

    <div class="avicore-home-hero__kpis">
        {{ $slot }}
    </div>
</section>
