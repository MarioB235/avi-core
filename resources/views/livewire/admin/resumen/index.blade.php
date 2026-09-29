<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Resumen"
        subtitle="Indicadores del día por granja y galpón."
    >
        <x-slot:actions>
            <a
                href="{{ route(auth()->user()->rol->routePrefix().'.reportes.produccion-diaria', array_filter([
                    'granja' => $filtroGranjaId !== '' ? $filtroGranjaId : null,
                    'galpon' => $filtroGalponId !== '' ? $filtroGalponId : null,
                ])) }}"
                class="avicore-btn avicore-btn--secondary avicore-btn--sm"
            >
                Exportar Excel (hoy)
            </a>
            <a
                href="{{ route(auth()->user()->rol->routePrefix().'.reportes.produccion-diaria-pdf', array_filter([
                    'granja' => $filtroGranjaId !== '' ? $filtroGranjaId : null,
                    'galpon' => $filtroGalponId !== '' ? $filtroGalponId : null,
                ])) }}"
                class="avicore-btn avicore-btn--secondary avicore-btn--sm"
            >
                Exportar PDF (hoy)
            </a>
        </x-slot:actions>
    </x-admin.page-hero>

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

                @if ($galponesFiltro->isNotEmpty())
                    <x-ui.select
                        label="Galpón"
                        name="filtroGalponId"
                        wire:model.live="filtroGalponId"
                        placeholder="Todos los galpones"
                        :options="$galponesOptions"
                    />
                @endif
            </div>
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
                    :hint="$huevosHoyUnidades.' · '.number_format($maplesHoy, 0, ',', '.').' maples'.(($huevosHoyDesglose['huevos'] ?? 0) > 0 ? ' + '.$huevosHoyDesglose['huevos'].' sueltos' : '')"
                    icon="egg"
                    tone="huevos"
                />

                <x-ui.stat-panel
                    label="Descarte hoy"
                    :value="number_format($resumen->huevosDescarteHoy, 0, ',', '.')"
                    hint="Huevos de descarte registrados hoy"
                    icon="egg"
                />

                <x-ui.stat-panel
                    label="Muertes hoy"
                    :value="number_format($resumen->muertesHoy, 0, ',', '.')"
                    hint="En los galpones filtrados"
                    icon="bird"
                />

                <x-ui.stat-panel
                    :label="$alimentoEntregado['etiqueta_kpi']"
                    :value="number_format($resumen->alimentoKgHoy, 0, ',', '.').' kg'"
                    :hint="$alimentoEntregado['hint_kpi']"
                    icon="truck"
                />

                <x-ui.stat-panel
                    label="Aves actuales"
                    :value="number_format($resumen->avesActuales, 0, ',', '.')"
                    :hint="$resumen->galponesActivos.' '.($resumen->galponesActivos === 1 ? 'galpón' : 'galpones')"
                    icon="warehouse"
                    tone="aves"
                />

                <x-ui.stat-panel
                    :label="$referenciaMortalidad['etiqueta_kpi']"
                    :value="number_format($resumen->alertasCount, 0, ',', '.')"
                    :hint="$referenciaMortalidad['etiqueta_umbral']"
                    icon="bell"
                />
            </div>

            <p class="mt-3 text-xs leading-relaxed text-avicore-muted">
                {{ $alimentoEntregado['disclaimer'] }}
            </p>

            <p class="mt-2 text-xs leading-relaxed text-avicore-muted">
                {{ $referenciaMortalidad['periodo'] }}
                {{ $referenciaMortalidad['disclaimer'] }}
            </p>
        </x-ui.reveal>

        <x-ui.reveal as="section" class="mt-8" aria-label="Semana operativa">
            <x-ui.section-head
                eyebrow="Tendencia"
                title="Semana operativa"
                subtitle="Últimos 7 días lógicos: aptos, descarte, muertes y kg entregados."
            />

            @include('livewire.admin.resumen.partials.semana-operativa', [
                'graficosSemanales' => $graficosSemanales,
            ])
        </x-ui.reveal>

        <x-ui.reveal as="section" class="mt-8" aria-label="Detalle por galpón">
            <x-ui.section-head
                eyebrow="Detalle"
                title="Por galpón"
                subtitle="Compará el día de hoy entre galpones."
            />

            @if ($resumen->galponesResumen === [])
                <x-ui.empty-state
                    class="mt-4"
                    title="Sin galpones activos"
                    description="Creá galpones en Estructura para ver indicadores aquí."
                />
            @else
                @include('livewire.admin.resumen.partials.galpones-table', [
                    'filas' => $resumen->galponesResumen,
                    'referenciaMortalidad' => $referenciaMortalidad,
                ])

                <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--duo mt-4 md:hidden">
                    @foreach ($resumen->galponesResumen as $fila)
                        @include('livewire.admin.resumen.partials.galpon-card', [
                            'fila' => $fila,
                            'referenciaMortalidad' => $referenciaMortalidad,
                        ])
                    @endforeach
                </div>
            @endif
        </x-ui.reveal>
    </div>
</div>
