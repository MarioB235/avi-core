<div class="avicore-operario-cargar">
    <x-operario.cargar-hero
        :galpon-etiqueta="$galponEtiqueta"
        :has-galpon="$galpon !== null"
    />

    <div class="avicore-operario-home-sheet">
        <section class="avicore-operario-cargar-types" aria-label="Tipos de carga">
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
                        Primero elegí un galpón en Inicio.
                    </p>
                </div>
            @endunless

            <div @class([
                'avicore-operario-carga-grid',
                'avicore-operario-carga-grid--quad' => $puedeRegistrarLote,
                'avicore-operario-carga-grid--triple' => ! $puedeRegistrarLote,
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
                    wire:click="abrirFormularioVacunacion"
                    @class([
                        'avicore-operario-carga-tile avicore-operario-carga-tile--action',
                        'avicore-operario-carga-tile--wide' => ! $puedeRegistrarLote,
                    ])
                >
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.illustration name="operario-vacuna" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Vacunación</span>
                    <span class="avicore-operario-carga-tile__badge">Registrá por lote</span>
                </button>

                @if ($puedeRegistrarLote)
                    {{-- avicore-defer: ilustración propia operario-lote cuando exista asset; hoy reutiliza operario-ave --}}
                    <button
                        type="button"
                        wire:click="abrirFormularioLote"
                        class="avicore-operario-carga-tile avicore-operario-carga-tile--action"
                    >
                        <span class="avicore-operario-carga-tile__icon">
                            <x-ui.illustration name="operario-ave" />
                        </span>
                        <span class="avicore-operario-carga-tile__label">Nuevo lote</span>
                        <span class="avicore-operario-carga-tile__badge">Ingresá aves al galpón</span>
                    </button>
                @endif
            </div>
        </section>
    </div>

    @if ($galpon)
        <x-ui.dialog wire:model="dialogHuevosAbierto" title="Huevos de hoy">
            @include('livewire.operario.partials.carga-huevos-form')
        </x-ui.dialog>

        <x-ui.dialog wire:model="dialogMuertesAbierto" title="Muertes de hoy">
            @include('livewire.operario.partials.carga-muertes-form')
        </x-ui.dialog>

        <x-ui.dialog wire:model="dialogVacunacionAbierto" title="Vacunación de hoy">
            @include('livewire.operario.partials.carga-vacunacion-form', [
                'lotesActivos' => $lotesActivos,
                'vacunas' => $vacunas,
            ])
        </x-ui.dialog>
    @endif

    @if ($puedeRegistrarLote)
        <x-ui.dialog wire:model="dialogLoteAbierto" title="Nuevo lote">
            @include('livewire.operario.partials.carga-lote-form', [
                'galponesDisponibles' => $galponesDisponibles,
            ])
        </x-ui.dialog>
    @endif
</div>
