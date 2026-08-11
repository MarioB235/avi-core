<div
    @class([
        'avicore-operario-cargar',
        'avicore-operario-cargar--selector-open' => $selectorGalponAbierto,
    ])
    x-data
    :class="{ 'avicore-operario-cargar--selector-open': $wire.selectorGalponAbierto }"
>
    <x-operario.cargar-hero
        :galpon-etiqueta="$galponEtiqueta"
        :has-galpon="$galpon !== null"
    >
        <x-slot:galponSelector>
            @include('livewire.operario.partials.galpon-chip-selector')
        </x-slot:galponSelector>
    </x-operario.cargar-hero>

    <div class="avicore-operario-home-sheet">
        <x-ui.reveal as="section" class="avicore-operario-cargar-types" aria-label="Tipos de carga">
            <div class="avicore-operario-home-section__head">
                <p class="avicore-operario-home-section__eyebrow">Carga del día</p>
                <h2 class="avicore-operario-home-section__title">¿Qué querés registrar?</h2>
            </div>

            @unless ($galpon)
                <div class="avicore-operario-cargar-alert" role="status">
                    <span class="avicore-operario-cargar-alert__icon" aria-hidden="true">
                        <x-ui.icon name="warehouse" class="size-5" />
                    </span>
                    <p class="avicore-operario-cargar-alert__text">
                        Primero elegí un galpón con el chip de arriba.
                    </p>
                </div>
            @endunless

            <div @class([
                'avicore-operario-carga-grid avicore-operario-carga-grid--quad',
            ])>
                <button
                    type="button"
                    wire:click="abrirFormularioHuevos"
                    class="avicore-operario-carga-tile avicore-operario-carga-tile--action"
                >
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.illustration name="operario-huevo" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Huevos</span>
                    <span class="avicore-operario-carga-tile__badge">Cuántos juntaste hoy</span>
                </button>

                <button
                    type="button"
                    wire:click="abrirFormularioMuertes"
                    class="avicore-operario-carga-tile avicore-operario-carga-tile--action"
                >
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.illustration name="operario-ave" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Muertes</span>
                    <span class="avicore-operario-carga-tile__badge">Cuántas aves murieron</span>
                </button>

                <button
                    type="button"
                    wire:click="abrirFormularioDescarte"
                    class="avicore-operario-carga-tile avicore-operario-carga-tile--action"
                >
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.illustration name="operario-ave" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Descarte</span>
                    <span class="avicore-operario-carga-tile__badge">Aves que sacaste vivas</span>
                </button>

                <button
                    type="button"
                    wire:click="abrirFormularioVacunacion"
                    class="avicore-operario-carga-tile avicore-operario-carga-tile--action"
                >
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.illustration name="operario-vacuna" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Vacunación</span>
                    <span class="avicore-operario-carga-tile__badge">Registrá por lote</span>
                </button>

                <button
                    type="button"
                    wire:click="abrirFormularioAlimento"
                    class="avicore-operario-carga-tile avicore-operario-carga-tile--action"
                >
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.icon name="truck" class="size-8 text-avicore-primary" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Alimento</span>
                    <span class="avicore-operario-carga-tile__badge">Entrega del camión</span>
                </button>

                @if ($puedeRegistrarLote)
                    {{-- avicore-defer: ilustración propia operario-lote cuando exista asset; hoy reutiliza operario-ave --}}
                    <button
                        type="button"
                        wire:click="abrirFormularioLote"
                        class="avicore-operario-carga-tile avicore-operario-carga-tile--action avicore-operario-carga-tile--wide"
                    >
                        <span class="avicore-operario-carga-tile__icon">
                            <x-ui.illustration name="operario-ave" />
                        </span>
                        <span class="avicore-operario-carga-tile__label">Nuevo lote</span>
                        <span class="avicore-operario-carga-tile__badge">Ingresá aves al galpón</span>
                    </button>
                @endif
            </div>
        </x-ui.reveal>
    </div>

    @if ($galpon && $dialogHuevosAbierto)
        <x-ui.dialog wire:model="dialogHuevosAbierto" title="Huevos de hoy">
            @include('livewire.operario.partials.carga-huevos-form')
        </x-ui.dialog>
    @endif

    @if ($galpon && $dialogMuertesAbierto)
        <x-ui.dialog wire:model="dialogMuertesAbierto" title="Muertes de hoy">
            @include('livewire.operario.partials.carga-muertes-form')
        </x-ui.dialog>
    @endif

    @if ($galpon && $dialogDescarteAbierto)
        <x-ui.dialog wire:model="dialogDescarteAbierto" title="Descarte de aves">
            @include('livewire.operario.partials.carga-descarte-form')
        </x-ui.dialog>
    @endif

    @if ($galpon && $dialogVacunacionAbierto)
        <x-ui.dialog wire:model="dialogVacunacionAbierto" title="Vacunación de hoy">
            @include('livewire.operario.partials.carga-vacunacion-form', [
                'lotesActivos' => $lotesActivos,
                'vacunas' => $vacunas,
            ])
        </x-ui.dialog>
    @endif

    @if ($galpon && $dialogAlimentoAbierto)
        <x-ui.dialog wire:model="dialogAlimentoAbierto" title="Entrega de alimento">
            @include('livewire.operario.partials.carga-alimento-form')
        </x-ui.dialog>
    @endif

    @if ($puedeRegistrarLote && $dialogLoteAbierto)
        <x-ui.dialog wire:model="dialogLoteAbierto" title="Nuevo lote">
            @include('livewire.operario.partials.carga-lote-form', [
                'galponesDisponibles' => $galponesDisponibles,
            ])
        </x-ui.dialog>
    @endif
</div>
