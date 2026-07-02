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

            @if ($hasGalpon)
                <div class="avicore-operario-home-hero__galpon avicore-operario-historial-hero__galpon">
                    <x-ui.icon name="warehouse" class="size-4 shrink-0" />
                    <span class="truncate">{{ $galponEtiqueta }}</span>
                </div>
            @else
                <a
                    href="{{ route('operario.home', ['abrir_galpon' => 1]) }}"
                    wire:navigate
                    class="avicore-operario-home-hero__galpon avicore-operario-home-hero__galpon--empty avicore-operario-historial-hero__galpon"
                >
                    <x-ui.icon name="warehouse" class="size-4 shrink-0" />
                    <span class="truncate">Sin seleccionar · Elegí en Inicio</span>
                </a>
            @endif
        </div>
    </div>
</section>
