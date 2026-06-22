<div class="avicore-operario-home">
    <x-operario.home-hero
        :saludo="$saludo"
        :galpon-etiqueta="$galponEtiqueta"
        :has-galpon="$galpon !== null"
    />

    <div class="avicore-operario-home-sheet">
        @if (session('status'))
            <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
        @endif

        <section class="avicore-operario-home-cargas" aria-label="Últimas cargas">
            <div class="avicore-operario-home-cargas__header">
                <h2 class="avicore-operario-home-section__title">Últimas cargas de hoy</h2>
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
                    <p class="avicore-operario-home-cargas__empty-text">
                        Todavía no hay cargas registradas hoy.
                    </p>
                @else
                    <ul class="avicore-operario-home-cargas__list">
                        @foreach ($ultimasCargas as $carga)
                            <li>
                                <a href="{{ route('operario.historial') }}" wire:navigate class="avicore-operario-load-item">
                                    <span class="avicore-operario-load-item__icon">
                                        <x-ui.icon name="egg" class="size-4" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-avicore-text">
                                            {{ $carga->tipo->label() }} · {{ $carga->cantidadResumen() }}
                                        </span>
                                        <span class="block truncate text-xs text-avicore-muted">
                                            {{ $carga->galpon?->displayName() }} · {{ $carga->created_at->format('H:i') }}
                                        </span>
                                    </span>
                                    <x-ui.icon name="chevron-right" class="avicore-operario-load-item__chevron size-4" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section class="avicore-operario-home-summary" aria-label="Resumen de hoy">
            <h2 class="avicore-operario-home-section__title">Resumen de hoy</h2>

            <div class="avicore-operario-kpi-grid">
                <article class="avicore-operario-kpi-card">
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
                <article class="avicore-operario-kpi-card">
                    <span class="avicore-operario-kpi-card__icon-shell">
                        <x-ui.icon name="trending-up" class="size-5" />
                    </span>
                    <p class="avicore-operario-kpi-card__value">N/D</p>
                    <p class="avicore-operario-kpi-card__label">Producción vs. objetivo</p>
                </article>
            </div>
        </section>
    </div>
</div>
