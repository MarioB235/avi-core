<div class="avicore-operario-historial">
    <x-operario.historial-hero
        :galpon-etiqueta="$galponEtiqueta"
        :has-galpon="$galpon !== null"
    />

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
                            @if ($fechaEtiqueta)
                                Registros del {{ $fechaEtiqueta }}
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
                <label for="historial-fecha" class="avicore-operario-historial-filter__label">
                    Ver otro día
                </label>
                <div class="avicore-operario-historial-filter__row">
                    <input
                        id="historial-fecha"
                        type="date"
                        wire:model.live="fecha"
                        class="avicore-operario-historial-filter__input"
                        max="{{ now()->format('Y-m-d') }}"
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
                @error('fecha')
                    <p class="text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
                @enderror
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
                                Cuando cargues huevos, muertes o vacunaciones, van a aparecer acá.
                            @endif
                        </p>
                    </div>
                @else
                    <ul class="avicore-operario-historial-list">
                        @foreach ($registros as $carga)
                            <li
                                wire:key="historial-{{ $carga->key }}"
                                @class([
                                    'avicore-operario-historial-list__item',
                                    'avicore-operario-historial-list__item--muertes' => $carga->esMortalidad,
                                    'avicore-operario-historial-list__item--vacunacion' => $carga->esVacunacion,
                                ])
                            >
                                <div class="avicore-operario-historial-list__copy min-w-0 flex-1">
                                    <p class="avicore-operario-historial-list__label">
                                        {{ $carga->label }}
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
</div>
