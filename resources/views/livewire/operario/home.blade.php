<div
    class="avicore-operario-home"
    x-data
    :class="{ 'avicore-operario-home--selector-open': $wire.selectorGalponAbierto }"
>
    <x-operario.home-hero :saludo="$saludo">
        <x-slot:galponSelector>
            @include('livewire.operario.partials.galpon-chip-selector')
        </x-slot:galponSelector>
    </x-operario.home-hero>

    <div class="avicore-operario-home-sheet">
        <x-operario.primary-action :href="route('operario.cargar')" />

        <section class="avicore-operario-home-summary" aria-label="Resumen de hoy">
            <div class="avicore-operario-home-section__head">
                <p class="avicore-operario-home-section__eyebrow">Hoy</p>
                <h2 class="avicore-operario-home-section__title">Resumen del día</h2>
            </div>

            <div class="avicore-operario-kpi-grid">
                <article class="avicore-operario-kpi-card avicore-operario-kpi-card--featured">
                    <span class="avicore-operario-kpi-card__icon-shell">
                        <x-ui.icon name="egg" class="size-5" />
                    </span>
                    <p class="avicore-operario-kpi-card__value">{{ number_format($maplesProducidosHoy, 0, ',', '.') }}</p>
                    <p class="avicore-operario-kpi-card__label">Maples hoy</p>
                </article>
                <article class="avicore-operario-kpi-card">
                    <span class="avicore-operario-kpi-card__icon-shell">
                        <x-ui.icon name="clipboard-list" class="size-5" />
                    </span>
                    <p class="avicore-operario-kpi-card__value">{{ $cargasCompletadasHoy }}</p>
                    <p class="avicore-operario-kpi-card__label">Cargas hoy</p>
                </article>
                {{-- avicore-defer: objetivo diario por galpón, cuando exista meta en reglas.md --}}
            </div>
        </section>

        <section
            @class([
                'avicore-operario-home-cargas',
                'avicore-operario-home-cargas--empty' => $ultimasCargas->isEmpty(),
            ])
            aria-label="Últimas cargas"
        >
            <div class="avicore-operario-home-cargas__header">
                <div class="avicore-operario-home-cargas__heading">
                    <span class="avicore-operario-home-cargas__heading-icon" aria-hidden="true">
                        <x-ui.icon name="clock" class="size-4" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="avicore-operario-home-section__title">Últimas cargas</h2>
                        <p class="avicore-operario-home-cargas__subtitle">Registros de hoy</p>
                    </div>
                </div>
                @if ($ultimasCargas->isNotEmpty())
                    <a href="{{ route('operario.historial') }}" wire:navigate class="avicore-operario-home-section__link">
                        Ver todo
                        <x-ui.icon name="chevron-right" class="size-3.5" />
                    </a>
                @endif
            </div>

            <div @class([
                'avicore-operario-home-cargas__body',
                'avicore-operario-home-cargas__body--empty' => $ultimasCargas->isEmpty(),
            ])>
                @if ($ultimasCargas->isEmpty())
                    <div class="avicore-operario-home-cargas__empty">
                        <span class="avicore-operario-home-cargas__empty-icon" aria-hidden="true">
                            <x-ui.icon name="clipboard-list" class="size-6" />
                        </span>
                        <div class="avicore-operario-home-cargas__empty-copy">
                            <p class="avicore-operario-home-cargas__empty-text">
                                Todavía no hay cargas hoy.
                            </p>
                            <p class="avicore-operario-home-cargas__empty-hint">
                                Registrá la primera carga del galpón.
                            </p>
                        </div>
                        <a
                            href="{{ route('operario.cargar') }}"
                            wire:navigate
                            class="avicore-operario-home-cargas__empty-cta"
                        >
                            Cargar ahora
                        </a>
                    </div>
                @else
                    <ul class="avicore-operario-home-cargas__list">
                        @foreach ($ultimasCargas as $carga)
                            <li>
                                <a href="{{ route('operario.historial') }}" wire:navigate class="avicore-operario-load-item">
                                    <span class="avicore-operario-load-item__icon">
                                        <x-ui.icon name="egg" class="size-4" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="avicore-operario-load-item__title">
                                            {{ $carga->tipo->label() }} · {{ $carga->cantidadResumen() }}
                                        </span>
                                        <span class="avicore-operario-load-item__meta">
                                            {{ $carga->galpon?->displayName() }}
                                        </span>
                                    </span>
                                    <span class="avicore-operario-load-item__time">
                                        {{ $carga->created_at->format('H:i') }}
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</div>
