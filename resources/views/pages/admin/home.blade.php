<x-layouts.admin
    title="Inicio · AviCore"
    heading="Inicio"
    :subheading="$home->contextLabel"
    :show-date="true"
    content-wide
    masthead
>
    <x-slot:hero>
        <x-admin.home-hero>
            <div class="avicore-admin-home-kpis">
                <x-ui.kpi-card
                    label="Producción de hoy"
                    value="Sin datos"
                    icon="trending-up"
                />
                <x-ui.kpi-card
                    label="Galpones activos"
                    value="Aún no configurado"
                    icon="warehouse"
                />
                <x-ui.kpi-card
                    label="Alertas"
                    value="Sin alertas"
                    icon="bell"
                />
                <x-ui.kpi-card
                    label="Usuarios activos"
                    :value="(string) $home->activeUsersCount"
                    :hint="$home->user->rol->label()"
                    icon="users"
                />
            </div>
        </x-admin.home-hero>
    </x-slot:hero>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="avicore-admin-home-panels">
        <x-ui.card class="avicore-panel-card">
            <div class="mb-5">
                <h2 class="avicore-section-title">Estado inicial</h2>
                <p class="avicore-section-subtitle">
                    Configurá los elementos básicos para comenzar a operar.
                </p>
            </div>

            <x-ui.setup-checklist :items="$home->setupItems" />

            <x-ui.button type="button" class="mt-6 w-full sm:w-auto" disabled>
                Configurar estructura
                <x-ui.icon name="arrow-right" class="size-4" />
            </x-ui.button>
        </x-ui.card>

        <x-ui.card class="avicore-panel-card">
            <div class="mb-2 flex items-center gap-2">
                <x-ui.icon name="clock" class="size-5 text-avicore-primary" />
                <h2 class="avicore-section-title">Actividad reciente</h2>
            </div>

            <x-ui.empty-state
                title="Aún no hay actividad registrada"
                description="Cuando comiences a realizar acciones en el sistema, las verás aquí."
                icon="clipboard-list"
            />
        </x-ui.card>
    </div>
</x-layouts.admin>
