<div class="avicore-operario-historial">
    <x-operario.historial-hero
        :galpon-etiqueta="$galponEtiqueta"
        :has-galpon="$galpon !== null"
    />

    <div class="avicore-operario-home-sheet">
        <section class="avicore-operario-home-cargas" aria-label="Historial de cargas">
            <div class="avicore-operario-home-cargas__header">
                <div class="avicore-operario-home-cargas__heading">
                    <span class="avicore-operario-home-cargas__heading-icon" aria-hidden="true">
                        <x-ui.icon name="clock" class="size-4" />
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
                            Huevos, muertes y más · del más reciente al anterior.
                        </p>
                    </div>
                </div>
            </div>

            <div class="avicore-operario-historial-filter">
                <label for="historial-fecha" class="avicore-operario-historial-filter__label">
                    Filtrar por fecha
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
                        <span class="avicore-operario-home-cargas__empty-icon" aria-hidden="true">
                            <x-ui.icon name="clipboard-list" class="size-6" />
                        </span>
                        <p class="avicore-operario-home-cargas__empty-text">
                            @if ($fecha)
                                No hay registros para esta fecha.
                            @else
                                Cuando registres huevos, muertes u otros datos, aparecerán acá.
                            @endif
                        </p>
                    </div>
                @else
                    <ul class="avicore-operario-historial-list">
                        @foreach ($registros as $carga)
                            <li
                                wire:key="historial-registro-{{ $carga->id }}"
                                @class([
                                    'avicore-operario-historial-list__item',
                                    'avicore-operario-historial-list__item--muertes' => $carga->esMortalidad(),
                                ])
                            >
                                <div class="avicore-operario-historial-list__copy min-w-0 flex-1">
                                    <p class="avicore-operario-historial-list__label">
                                        {{ $carga->cantidadResumen() }}
                                    </p>
                                    @if ($carga->observacion)
                                        <p class="avicore-operario-historial-list__note">
                                            {{ $carga->observacion }}
                                        </p>
                                    @endif
                                </div>
                                <time
                                    class="avicore-operario-historial-list__time"
                                    datetime="{{ $carga->created_at->toIso8601String() }}"
                                >
                                    @if ($fecha)
                                        {{ $carga->created_at->format('H:i') }}
                                    @else
                                        {{ $carga->created_at->format('d/m H:i') }}
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
