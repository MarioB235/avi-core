<div class="avicore-operario-historial">
    <x-operario.historial-hero
        :galpon-etiqueta="$galponEtiqueta"
        :has-galpon="$galpon !== null"
    />

    <div class="avicore-operario-home-sheet">
        <section class="avicore-operario-home-cargas" aria-label="Registros de hoy">
            <div class="avicore-operario-home-cargas__header">
                <div class="avicore-operario-home-cargas__heading">
                    <span class="avicore-operario-home-cargas__heading-icon" aria-hidden="true">
                        <x-ui.icon name="clock" class="size-4" />
                    </span>
                    <div class="min-w-0">
                        <p class="avicore-operario-home-section__eyebrow">Hoy</p>
                        <h2 class="avicore-operario-home-section__title">Registros del día</h2>
                    </div>
                </div>
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
                            Cuando registres huevos u otros datos, aparecerán acá.
                        </p>
                    </div>
                @else
                    <ul class="avicore-operario-home-cargas__list">
                        @foreach ($ultimasCargas as $carga)
                            <li>
                                <div class="avicore-operario-load-item avicore-operario-load-item--static">
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
                                        @if ($carga->observacion)
                                            <span class="avicore-operario-load-item__meta mt-0.5 block">
                                                {{ $carga->observacion }}
                                            </span>
                                        @endif
                                    </span>
                                    <span class="avicore-operario-load-item__time">
                                        {{ $carga->created_at->format('H:i') }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section class="avicore-operario-historial-account" aria-label="Tu cuenta">
            <div class="avicore-operario-home-section__head">
                <p class="avicore-operario-home-section__eyebrow">Sesión</p>
                <h2 class="avicore-operario-home-section__title">Tu cuenta</h2>
            </div>

            <div class="avicore-operario-historial-account__card">
                <p class="text-sm font-semibold text-avicore-text">{{ auth()->user()->name }}</p>
                <p class="mt-0.5 text-xs text-avicore-muted">{{ auth()->user()->documento }}</p>

                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <x-ui.button type="submit" variant="secondary" class="w-full">
                        Cerrar sesión
                    </x-ui.button>
                </form>
            </div>
        </section>
    </div>
</div>
