<div class="avicore-operario-cargar">
    <x-operario.cargar-hero
        :galpon-etiqueta="$galponEtiqueta"
        :has-galpon="$galpon !== null"
    />

    <div class="avicore-operario-home-sheet">
        <section class="avicore-operario-cargar-types" aria-label="Tipos de carga">
            <div class="avicore-operario-home-section__head">
                <p class="avicore-operario-home-section__eyebrow">Registro</p>
                <h2 class="avicore-operario-home-section__title">Tipo de carga</h2>
            </div>

            @unless ($galpon)
                <div class="avicore-operario-cargar-alert" role="status">
                    <span class="avicore-operario-cargar-alert__icon" aria-hidden="true">
                        <x-ui.icon name="warehouse" class="size-5" />
                    </span>
                    <p class="avicore-operario-cargar-alert__text">
                        Elegí un galpón en Inicio antes de cargar datos.
                    </p>
                </div>
            @endunless

            <div class="avicore-operario-carga-grid">
                <button
                    type="button"
                    wire:click="abrirFormularioHuevos"
                    class="avicore-operario-carga-tile avicore-operario-carga-tile--action"
                >
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.icon name="egg" class="size-6" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Huevos</span>
                    <span class="avicore-operario-carga-tile__badge">Producción del día</span>
                </button>

                <div class="avicore-operario-carga-tile avicore-operario-carga-tile--soon" aria-disabled="true">
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.icon name="users" class="size-6" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Muertes</span>
                    <span class="avicore-operario-carga-tile__badge">Próximamente</span>
                </div>

                <div class="avicore-operario-carga-tile avicore-operario-carga-tile--soon" aria-disabled="true">
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.icon name="layers" class="size-6" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Alimento</span>
                    <span class="avicore-operario-carga-tile__badge">Próximamente</span>
                </div>

                <div class="avicore-operario-carga-tile avicore-operario-carga-tile--soon" aria-disabled="true">
                    <span class="avicore-operario-carga-tile__icon">
                        <x-ui.icon name="clipboard-list" class="size-6" />
                    </span>
                    <span class="avicore-operario-carga-tile__label">Combinada</span>
                    <span class="avicore-operario-carga-tile__badge">Próximamente</span>
                </div>
            </div>
        </section>
    </div>

    @if ($galpon)
        <x-ui.dialog wire:model="dialogHuevosAbierto" title="Carga de huevos">
            @include('livewire.operario.partials.carga-huevos-form')
        </x-ui.dialog>
    @endif
</div>
