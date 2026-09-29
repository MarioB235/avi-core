@php
    $hora = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $inicio = $home->inicio;
    $pulso = $home->pulso;
    $stockPreview = $home->stockPreview;
    $onboarding = $home->onboarding;
@endphp

<x-layouts.admin title="Inicio · AviCore">
    <div class="avicore-operario-home">
        <x-admin.home-hero
            :saludo="$saludo"
            :subtitle="$home->contextLabel.'.'"
        />

        <div class="avicore-operario-home-sheet">
            @if ($onboarding['show'])
                <x-ui.reveal as="section" aria-label="{{ $onboarding['title'] }}">
                    <x-ui.section-head
                        eyebrow="Configuración"
                        :title="$onboarding['title']"
                        :subtitle="$onboarding['subtitle']"
                    />

                    <x-ui.setup-checklist class="mt-4" :items="$onboarding['items']" />
                </x-ui.reveal>
            @endif

            @if ($inicio['show_estructura'])
                @if ($pulso['show'])
                    <x-ui.reveal as="section" aria-label="Tu empresa hoy">
                        <x-ui.section-head
                            eyebrow="Hoy"
                            title="Tu empresa hoy"
                            subtitle="Pulso del día en campo — producción y alertas."
                        />

                        <x-ui.pulse-panel class="mt-4" :pulso="$pulso" />

                        <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--duo mt-4">
                            <x-ui.stat-panel
                                label="Huevos juntados hoy"
                                :value="number_format($pulso['huevos_hoy'], 0, ',', '.')"
                                :hint="$pulso['unidades_cajas_maples']"
                                icon="egg"
                                tone="huevos"
                            />

                            <x-ui.stat-panel
                                label="Muertes hoy"
                                :value="number_format($pulso['muertes_hoy'], 0, ',', '.')"
                                hint="En todos los galpones activos"
                                icon="bird"
                            />
                        </div>

                        @if ($pulso['galpones_sin_carga'] !== [] || $pulso['alertas'] !== [])
                            <div class="avicore-pulse-list mt-4" role="list">
                                @foreach ($pulso['alertas'] as $alerta)
                                    <div
                                        class="avicore-pulse-list__item avicore-pulse-list__item--alert"
                                        role="listitem"
                                    >
                                        <span class="avicore-pulse-list__name">
                                            {{ $alerta['nombre'] }} — mortalidad acum. sobre referencia
                                        </span>
                                        <span class="avicore-pulse-list__meta">
                                            {{ $alerta['granja'] }} · {{ number_format($alerta['mortalidad_pct'], 1, ',', '.') }}% acumulado
                                        </span>
                                    </div>
                                @endforeach

                                @foreach ($pulso['galpones_sin_carga'] as $galpon)
                                    <div class="avicore-pulse-list__item" role="listitem">
                                        <span class="avicore-pulse-list__name">
                                            {{ $galpon['nombre'] }} — capturas productivas pendientes
                                        </span>
                                        <span class="avicore-pulse-list__meta">{{ $galpon['granja'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($pulso['resumen_route'])
                            <p class="mt-4">
                                <a
                                    href="{{ route($pulso['resumen_route']) }}"
                                    class="text-sm font-medium text-avicore-primary no-underline md:hover:underline"
                                    wire:navigate
                                >
                                    Ver análisis completo en Resumen →
                                </a>
                            </p>
                        @endif
                    </x-ui.reveal>
                @endif

                <x-ui.reveal as="section" class="{{ $pulso['show'] ? 'mt-8' : '' }}" aria-label="Tu empresa">
                    <x-ui.section-head
                        eyebrow="Empresa"
                        title="Tu empresa"
                        subtitle="Panorama de granjas y galpones activos."
                    />

                    @if ($inicio['granjas'] === 0 && $inicio['galpones'] === 0)
                        <x-ui.empty-state
                            class="mt-4"
                            title="Sin estructura cargada"
                            description="Cuando administración configure granjas y galpones, vas a ver el panorama acá."
                            icon="warehouse"
                        />
                    @else
                        <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--duo mt-4">
                            <x-ui.stat-panel
                                label="Granjas activas"
                                :value="number_format($inicio['granjas'], 0, ',', '.')"
                                hint="Unidades productivas habilitadas"
                                icon="layers"
                            />

                            <x-ui.stat-panel
                                label="Galpones activos"
                                :value="number_format($inicio['galpones'], 0, ',', '.')"
                                hint="Puestos de carga en campo"
                                icon="warehouse"
                            />
                        </div>
                    @endif
                </x-ui.reveal>

                @if ($stockPreview['show'])
                    <x-ui.reveal as="section" class="mt-8" aria-label="Stock y demanda">
                        <x-ui.section-head
                            eyebrow="Vista previa"
                            title="Stock y demanda"
                            subtitle="Reserva en cámara, pedidos y salida del día — datos de ejemplo hasta el módulo comercial."
                        />

                        <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--stat mt-4">
                            @foreach ($stockPreview['items'] as $item)
                                <x-ui.stat-panel
                                    :label="$item['label']"
                                    :value="$item['value']"
                                    :hint="$item['hint']"
                                    :icon="$item['icon'] ?? null"
                                    :tone="$item['tone'] ?? 'default'"
                                />
                            @endforeach
                        </div>
                    </x-ui.reveal>
                @endif
            @else
                <x-ui.reveal as="section" aria-label="Panel de gestión">
                    <x-ui.section-head
                        eyebrow="Gestión"
                        title="Panel AviCore"
                        subtitle="Elegí un módulo en el menú."
                    />

                    <x-ui.empty-state
                        class="mt-4"
                        title="Sin indicadores operativos"
                        description="Este rol no tiene resumen de producción en el panel."
                        icon="chart"
                    />
                </x-ui.reveal>
            @endif
        </div>
    </div>
</x-layouts.admin>
