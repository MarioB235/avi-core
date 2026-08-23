@php
    $hora = now()->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
    $teaser = $home->operativoTeaser;
@endphp

<x-layouts.admin title="Inicio · AviCore">
    <div class="avicore-operario-home">
        <x-admin.home-hero
            :saludo="$saludo"
            :subtitle="'Resumen de '.$home->contextLabel.'.'"
        />

        <div class="avicore-operario-home-sheet">
            <x-ui.reveal as="section" aria-label="Producción de hoy">
                <x-ui.section-head
                    eyebrow="Hoy"
                    title="Producción de hoy"
                    subtitle="Lo que pasó hoy en tus galpones."
                />

                <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--stat mt-4">
                    <x-ui.stat-panel
                        label="Huevos juntados"
                        :value="number_format($teaser['huevos_hoy'], 0, ',', '.')"
                        hint="Total de hoy en todos los galpones"
                        illustration="operario-huevo"
                        tone="huevos"
                    />

                    <x-ui.stat-panel
                        label="Aves que murieron"
                        :value="number_format($teaser['muertes_hoy'], 0, ',', '.')"
                        hint="Registradas hoy en campo"
                        illustration="operario-muertes"
                    />

                    <x-ui.stat-panel
                        label="Galpones en alerta"
                        :value="number_format($teaser['alertas_count'], 0, ',', '.')"
                        hint="Mortalidad por encima de lo normal"
                        icon="bell"
                    />

                    <x-ui.stat-panel
                        label="Galpones con producción"
                        :value="number_format($teaser['galpones_activos'], 0, ',', '.')"
                        hint="Con lotes activos ahora"
                        illustration="operario-ave"
                        tone="aves"
                    />
                </div>
            </x-ui.reveal>
        </div>
    </div>
</x-layouts.admin>
