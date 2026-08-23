<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Equipo"
        subtitle="Tu gente en AviCore — solo lectura. La gestión la hace administración."
    />

    <div class="avicore-operario-home-sheet">
        <x-ui.reveal as="section" aria-label="Resumen del equipo">
            <x-ui.section-head
                eyebrow="Personas"
                title="Tu gente en AviCore"
                :subtitle="'Resumen de '.$contextLabel.'.'"
            />

            <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--stat mt-4">
                @foreach ($items as $item)
                    <x-ui.stat-panel
                        :label="$item['label']"
                        :value="$item['value']"
                        :hint="$item['hint']"
                        :icon="$item['icon'] ?? null"
                    />
                @endforeach
            </div>
        </x-ui.reveal>
    </div>
</div>
