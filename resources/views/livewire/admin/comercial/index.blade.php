<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Comercial"
        subtitle="Clientes, ventas y huevos comprometidos módulo en preparación."
    />

    <div class="avicore-operario-home-sheet">
        <x-ui.reveal as="section" aria-label="Vista previa comercial">
            <x-ui.section-head
                eyebrow="Próximamente"
                title="Clientes y entregas"
                subtitle="Ventas, pedidos y huevos que ya tenés comprometidos."
            />

            <div class="avicore-operario-kpi-grid avicore-operario-kpi-grid--stat mt-4">
                @foreach ($items as $item)
                    <x-ui.stat-panel
                        :label="$item['label']"
                        :value="$item['value']"
                        :hint="$item['hint']"
                        :icon="$item['icon'] ?? null"
                        :illustration="$item['illustration'] ?? null"
                        :tone="$item['tone'] ?? 'default'"
                    />
                @endforeach
            </div>
        </x-ui.reveal>
    </div>
</div>
