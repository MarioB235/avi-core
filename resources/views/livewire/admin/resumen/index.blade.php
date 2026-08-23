<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Resumen"
        subtitle="Indicadores del día por granja y galpón."
    />

    <div class="avicore-operario-home-sheet">
        <x-ui.reveal as="section" aria-label="Filtros">
            <div class="grid gap-3 sm:max-w-md">
                <x-ui.select
                    label="Granja"
                    name="filtroGranjaId"
                    wire:model.live="filtroGranjaId"
                    placeholder="Todas las granjas"
                    :options="$granjasOptions"
                />
            </div>

            @if ($galponesFiltro->isNotEmpty())
                <div class="mt-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium text-avicore-text">Galpones</p>
                        @if ($filtroGalponIds !== [])
                            <button
                                type="button"
                                wire:click="limpiarFiltroGalpones"
                                class="text-sm font-medium text-avicore-primary md:hover:underline"
                            >
                                Ver todos
                            </button>
                        @endif
                    </div>

                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($galponesFiltro as $galpon)
                            <button
                                type="button"
                                wire:click="toggleGalpon({{ $galpon->id }})"
                                class="avicore-operario-filter-chip {{ in_array($galpon->id, $filtroGalponIds, true) ? 'avicore-operario-filter-chip--active' : 'avicore-operario-filter-chip--idle' }}"
                                aria-pressed="{{ in_array($galpon->id, $filtroGalponIds, true) ? 'true' : 'false' }}"
                            >
                                {{ $galpon->nombre }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-ui.reveal>

        <x-ui.reveal as="section" class="mt-8" aria-label="Indicadores del día">
            <x-ui.section-head
                eyebrow="Hoy"
                title="Indicadores del día"
            />

            <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--stat mt-4">
                <x-ui.stat-panel
                    label="Huevos hoy"
                    :value="number_format($resumen->huevosHoy, 0, ',', '.')"
                    hint="Aptos registrados hoy"
                    icon="egg"
                    tone="huevos"
                />

                <x-ui.stat-panel
                    label="Muertes hoy"
                    :value="number_format($resumen->muertesHoy, 0, ',', '.')"
                    hint="En los galpones filtrados"
                    icon="bird"
                />

                <x-ui.stat-panel
                    label="Aves actuales"
                    :value="number_format($resumen->avesActuales, 0, ',', '.')"
                    :hint="$resumen->galponesActivos.' '.($resumen->galponesActivos === 1 ? 'galpón' : 'galpones')"
                    icon="warehouse"
                    tone="aves"
                />

                <x-ui.stat-panel
                    label="Alertas mortalidad"
                    :value="number_format($resumen->alertasCount, 0, ',', '.')"
                    :hint="'Sobre '.number_format($mortalidadReferencia, 1, ',', '.').'% acumulado'"
                    icon="bell"
                />
            </div>
        </x-ui.reveal>

        <x-ui.reveal as="section" class="mt-8" aria-label="Detalle por galpón">
            <x-ui.section-head
                eyebrow="Detalle"
                title="Por galpón"
            />

            @if ($resumen->galponesResumen === [])
                <x-ui.empty-state
                    class="mt-4"
                    title="Sin galpones activos"
                    description="Creá galpones en Estructura para ver indicadores aquí."
                />
            @else
                <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--duo mt-4">
                    @foreach ($resumen->galponesResumen as $fila)
                        @include('livewire.admin.resumen.partials.galpon-card', ['fila' => $fila])
                    @endforeach
                </div>
            @endif
        </x-ui.reveal>
    </div>
</div>
