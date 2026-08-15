<div
    class="avicore-operario-historial"
    x-data
    :class="{ 'avicore-operario-historial--selector-open': $wire.selectorGalponAbierto }"
>
    <x-operario.historial-hero
        :galpon-etiqueta="$galponEtiqueta"
        :has-galpon="$galpon !== null"
    >
        <x-slot:galponSelector>
            @include('livewire.operario.partials.galpon-chip-selector')
        </x-slot:galponSelector>
    </x-operario.historial-hero>

    <div class="avicore-operario-home-sheet">
        <section class="avicore-operario-home-cargas" aria-label="Historial de cargas">
            <div class="avicore-operario-home-cargas__header">
                <div class="avicore-operario-home-cargas__heading">
                    <span class="avicore-operario-carga-tile__icon" aria-hidden="true">
                        <x-ui.illustration name="operario-reloj" />
                    </span>
                    <div class="min-w-0">
                        <p class="avicore-operario-home-section__eyebrow">Tus cargas</p>
                        <h2 class="avicore-operario-home-section__title">
                            @if ($fecha)
                                Registros del día
                            @else
                                Todos los registros
                            @endif
                        </h2>
                        <p class="avicore-operario-home-cargas__subtitle">
                            Del más reciente al más antiguo.
                        </p>
                    </div>
                </div>
            </div>

            <div class="avicore-operario-historial-filter">
                <label for="historial-fecha" class="sr-only">Fecha</label>
                <div class="avicore-operario-historial-filter__row">
                    <x-ui.date-picker
                        id="historial-fecha"
                        name="fecha"
                        placeholder="Elegí un día"
                        panel-title="Elegí un día"
                        wire:model.live="fecha"
                        :max="now()->toDateString()"
                        :error="$fechaError"
                        class="avicore-operario-historial-filter__picker"
                    />
                    @if ($fecha)
                        <button
                            type="button"
                            wire:click="verTodasLasFechas"
                            class="avicore-operario-historial-filter__clear"
                        >
                            Ver todas
                        </button>
                    @endif
                </div>
            </div>

            <div @class([
                'avicore-operario-home-cargas__body',
                'avicore-operario-home-cargas__body--empty' => $registros->isEmpty(),
            ])>
                @if ($registros->isEmpty())
                    <div class="avicore-operario-home-cargas__empty">
                        <span class="avicore-operario-carga-tile__icon" aria-hidden="true">
                            <x-ui.illustration name="operario-reloj" />
                        </span>
                        <p class="avicore-operario-home-cargas__empty-text">
                            @if ($fecha)
                                No hay registros para esta fecha.
                            @else
                                Cuando cargues huevos, muertes, descarte, alimento o vacunaciones, van a aparecer acá.
                            @endif
                        </p>
                    </div>
                @else
                    @if ($todosRegistrosAnulados)
                        <p class="avicore-operario-historial-notice" role="status">
                            Todos los registros de este listado están anulados y no cuentan en los totales del galpón.
                        </p>
                    @endif

                    <ul class="avicore-operario-historial-list">
                        @foreach ($registros as $carga)
                            <li wire:key="historial-{{ $carga->key }}">
                                <button
                                    type="button"
                                    wire:click="abrirDetalle('{{ $carga->key }}')"
                                    @class([
                                        'avicore-operario-historial-list__item avicore-operario-historial-list__item--action',
                                        'avicore-operario-historial-list__item--muertes' => $carga->esMortalidad && ! $carga->anulado,
                                        'avicore-operario-historial-list__item--vacunacion' => $carga->esVacunacion && ! $carga->anulado,
                                        'avicore-operario-historial-list__item--anulado' => $carga->anulado,
                                    ])
                                >
                                    <div class="avicore-operario-historial-list__copy min-w-0 flex-1">
                                        <p class="avicore-operario-historial-list__label">
                                            {{ $carga->label }}
                                            @if ($carga->anulado)
                                                <span class="avicore-operario-historial-list__badge">Anulado</span>
                                            @endif
                                        </p>
                                        <p class="avicore-operario-historial-list__meta">
                                            <span>{{ $carga->tipoEtiqueta }}</span>
                                            <span aria-hidden="true">·</span>
                                            <span>{{ $carga->galponEtiqueta }}</span>
                                        </p>
                                        @if ($carga->observacion)
                                            <p class="avicore-operario-historial-list__note">
                                                {{ $carga->observacion }}
                                            </p>
                                        @endif
                                    </div>
                                    <time
                                        class="avicore-operario-historial-list__time"
                                        datetime="{{ $carga->createdAt->toIso8601String() }}"
                                    >
                                        @if ($fecha)
                                            {{ $carga->createdAt->format('H:i') }}
                                        @else
                                            {{ $carga->createdAt->format('d/m H:i') }}
                                        @endif
                                    </time>
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    @if ($registros->hasPages())
                        <div class="avicore-operario-historial-pagination">
                            {{ $registros->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </section>
    </div>

    @include('livewire.operario.partials.historial-detalle-dialog', ['detalleItem' => $detalleItem])
</div>
