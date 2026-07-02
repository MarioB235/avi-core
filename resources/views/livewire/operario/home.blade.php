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
                <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--duo">
                    <article class="avicore-operario-kpi-panel avicore-operario-kpi-panel--aves">
                        <header class="avicore-operario-kpi-panel__head">
                            <span class="avicore-operario-carga-tile__icon" aria-hidden="true">
                                <x-ui.illustration name="operario-ave" />
                            </span>
                            <h3 class="avicore-operario-kpi-panel__title">Aves</h3>
                        </header>

                        <div class="avicore-operario-kpi-panel__metrics">
                            <div class="avicore-operario-kpi-panel__metric avicore-operario-kpi-panel__metric--alive">
                                <p class="avicore-operario-kpi-panel__value">
                                    {{ number_format($resumen['aves_actuales'], 0, ',', '.') }}
                                </p>
                                <p class="avicore-operario-kpi-panel__label">En el galpón ahora</p>
                            </div>

                            <div @class([
                                'avicore-operario-kpi-panel__metric',
                                'avicore-operario-kpi-panel__metric--warm',
                                'avicore-operario-kpi-panel__metric--warm-alert' => $resumen['muertes_hoy'] > 0,
                            ])>
                                <p class="avicore-operario-kpi-panel__value">
                                    {{ number_format($resumen['muertes_hoy'], 0, ',', '.') }}
                                </p>
                                <p class="avicore-operario-kpi-panel__label">Murieron hoy</p>
                            </div>
                        </div>

                        @if ($resumen['muertes_acumuladas'] > 0)
                            <p class="avicore-operario-kpi-panel__note">
                                <span class="avicore-operario-kpi-panel__note-dot" aria-hidden="true"></span>
                                {{ number_format($resumen['muertes_acumuladas'], 0, ',', '.') }} muertes en total desde el ingreso.
                            </p>
                        @endif
                    </article>

                    <article class="avicore-operario-kpi-panel avicore-operario-kpi-panel--huevos">
                        <header class="avicore-operario-kpi-panel__head">
                            <span class="avicore-operario-carga-tile__icon" aria-hidden="true">
                                <x-ui.illustration name="operario-huevo" />
                            </span>
                            <h3 class="avicore-operario-kpi-panel__title">Huevos</h3>
                        </header>

                        <div class="avicore-operario-kpi-panel__metrics">
                            <div class="avicore-operario-kpi-panel__metric avicore-operario-kpi-panel__metric--outline">
                                <p class="avicore-operario-kpi-panel__value">
                                    {{ number_format($resumen['huevos_hoy'], 0, ',', '.') }}
                                </p>
                                <p class="avicore-operario-kpi-panel__label">
                                    Juntados hoy
                                    <span class="avicore-operario-kpi-panel__hint">
                                        ({{ number_format($resumen['maples_hoy'], 0, ',', '.') }} maples)
                                    </span>
                                </p>
                            </div>

                            <div class="avicore-operario-kpi-panel__metric avicore-operario-kpi-panel__metric--outline">
                                <p class="avicore-operario-kpi-panel__value">
                                    {{ number_format($resumen['huevos_acumulados'], 0, ',', '.') }}
                                </p>
                                <p class="avicore-operario-kpi-panel__label">
                                    Total del lote
                                    <span class="avicore-operario-kpi-panel__hint">
                                        ({{ number_format($resumen['maples_acumulados'], 0, ',', '.') }} maples)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="avicore-operario-home-lotes" aria-label="Lotes en galpón">
                <div class="avicore-operario-home-section__head">
                    <p class="avicore-operario-home-section__eyebrow">Lotes</p>
                    <h2 class="avicore-operario-home-section__title">Activos en este galpón</h2>
                </div>

                @if ($resumen['multiples_lotes'])
                    <p class="avicore-operario-home-lotes__notice">
                        Hay más de un lote. Los números se cuentan para todo el galpón junto.
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
                                        {{ number_format($lote->cantidad_inicial, 0, ',', '.') }} aves
                                        · desde el {{ $lote->fecha_ingreso->format('d/m/Y') }}
                                        · {{ $edadSemanasPorLote[$lote->id] ?? 0 }} semanas
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
