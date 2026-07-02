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
        @if ($galpon === null)
            <section class="avicore-operario-home-summary avicore-operario-home-summary--empty" aria-label="Estado del galpón">
                <div class="avicore-operario-home-summary__empty">
                    <p class="avicore-operario-home-summary__empty-text">
                        Seleccioná un galpón para ver el estado.
                    </p>
                    <button
                        type="button"
                        wire:click="toggleSelectorGalpon"
                        class="avicore-operario-home-summary__empty-action"
                    >
                        Elegir galpón
                    </button>
                </div>
            </section>
        @else
            <section class="avicore-operario-home-summary" aria-label="Estado del galpón">
                <div class="avicore-operario-kpi-grid">
                    <article class="avicore-operario-kpi-card avicore-operario-kpi-card--featured">
                        <span class="avicore-operario-kpi-card__icon-shell">
                            <x-ui.icon name="users" class="size-5" />
                        </span>
                        <p class="avicore-operario-kpi-card__value">
                            {{ number_format($resumen['aves_actuales'], 0, ',', '.') }}
                        </p>
                        <p class="avicore-operario-kpi-card__label">Aves actuales</p>
                    </article>

                    <article class="avicore-operario-kpi-card">
                        <span class="avicore-operario-kpi-card__icon-shell">
                            <x-ui.icon name="egg" class="size-5" />
                        </span>
                        <p class="avicore-operario-kpi-card__value">
                            {{ number_format($resumen['huevos_hoy'], 0, ',', '.') }}
                        </p>
                        <p class="avicore-operario-kpi-card__label">
                            Huevos hoy
                            <span class="avicore-operario-kpi-card__hint">
                                ({{ number_format($resumen['maples_hoy'], 0, ',', '.') }} maples)
                            </span>
                        </p>
                    </article>

                    <article @class([
                        'avicore-operario-kpi-card',
                        'avicore-operario-kpi-card--danger' => $resumen['muertes_hoy'] > 0,
                    ])>
                        <span class="avicore-operario-kpi-card__icon-shell">
                            <x-ui.icon name="bell" class="size-5" />
                        </span>
                        <p class="avicore-operario-kpi-card__value">
                            {{ number_format($resumen['muertes_hoy'], 0, ',', '.') }}
                        </p>
                        <p class="avicore-operario-kpi-card__label">Muertes hoy</p>
                    </article>

                    <article class="avicore-operario-kpi-card">
                        <span class="avicore-operario-kpi-card__icon-shell">
                            <x-ui.icon name="clipboard-list" class="size-5" />
                        </span>
                        <p class="avicore-operario-kpi-card__value">
                            {{ number_format($resumen['huevos_acumulados'], 0, ',', '.') }}
                        </p>
                        <p class="avicore-operario-kpi-card__label">
                            Producción acumulada
                            <span class="avicore-operario-kpi-card__hint">desde ingreso del lote</span>
                        </p>
                    </article>
                </div>

                @if ($resumen['muertes_acumuladas'] > 0)
                    <p class="avicore-operario-home-summary__footnote avicore-operario-home-summary__footnote--danger">
                        {{ number_format($resumen['muertes_acumuladas'], 0, ',', '.') }} muertes acumuladas desde ingreso del lote.
                    </p>
                @endif
            </section>

            <section class="avicore-operario-home-lotes" aria-label="Lotes en galpón">
                <div class="avicore-operario-home-section__head">
                    <p class="avicore-operario-home-section__eyebrow">Lotes</p>
                    <h2 class="avicore-operario-home-section__title">En producción</h2>
                </div>

                @if ($resumen['multiples_lotes'])
                    <p class="avicore-operario-home-lotes__notice">
                        Este galpón tiene más de un lote activo. La producción se asigna al galpón completo.
                    </p>
                @endif

                @if ($resumen['lotes']->isEmpty())
                    <p class="avicore-operario-home-lotes__empty">
                        No hay lotes activos en este galpón.
                    </p>
                @else
                    <ul class="avicore-operario-home-lotes__list">
                        @foreach ($resumen['lotes'] as $lote)
                            <li wire:key="home-lote-{{ $lote->id }}" class="avicore-operario-home-lotes__item">
                                <div class="avicore-operario-home-lotes__copy min-w-0 flex-1">
                                    <p class="avicore-operario-home-lotes__code">{{ $lote->codigo }}</p>
                                    <p class="avicore-operario-home-lotes__meta">
                                        {{ $edadSemanasPorLote[$lote->id] ?? 0 }} semanas
                                        · {{ number_format($lote->cantidad_inicial, 0, ',', '.') }} inicio
                                    </p>
                                </div>
                                <span class="avicore-operario-home-lotes__badge">
                                    {{ $lote->estado->label() }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif
    </div>
</div>
