<div
    class="avicore-operario-home"
    x-data
    :class="{ 'avicore-operario-home--selector-open': $wire.selectorGalponAbierto }"
>
    <x-operario.home-hero :saludo="$saludo" :primer-nombre="$primerNombre">
        <x-slot:galponSelector>
            @include('livewire.operario.partials.galpon-chip-selector')
        </x-slot:galponSelector>
    </x-operario.home-hero>

    <div class="avicore-operario-home-sheet">
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
                    <p class="avicore-operario-kpi-card__label">Maples producidos</p>
                </article>
                <article class="avicore-operario-kpi-card">
                    <span class="avicore-operario-kpi-card__icon-shell">
                        <x-ui.icon name="clipboard-list" class="size-5" />
                    </span>
                    <p class="avicore-operario-kpi-card__value">{{ $cargasCompletadasHoy }}</p>
                    <p class="avicore-operario-kpi-card__label">Cargas realizadas</p>
                </article>
                <article class="avicore-operario-kpi-card avicore-operario-kpi-card--muted">
                    <span class="avicore-operario-kpi-card__icon-shell">
                        <x-ui.icon name="trending-up" class="size-5" />
                    </span>
                    <p class="avicore-operario-kpi-card__value">N/D</p>
                    <p class="avicore-operario-kpi-card__label">Producción vs. objetivo</p>
                </article>
            </div>
        </section>

        <section class="avicore-operario-home-cargas" aria-label="Últimas cargas">
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
                        <p class="avicore-operario-home-cargas__empty-text">
                            Todavía no hay cargas registradas hoy.
                        </p>
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
