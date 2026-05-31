<x-layouts.admin title="Panel · AviCore" heading="Base administrativa" subheading="Componentes UI iniciales">
    <div class="grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Producción hoy">
            <p class="avicore-kpi-value text-avicore-primary">12.480</p>
            <p class="mt-1 text-sm text-avicore-muted">Huevos registrados</p>
        </x-ui.card>

        <x-ui.card title="Estado">
            <div class="flex flex-wrap gap-2">
                <x-ui.badge variant="success">Activo</x-ui.badge>
                <x-ui.badge variant="warning">Alerta</x-ui.badge>
            </div>
        </x-ui.card>

        <x-ui.card title="Acciones">
            <div class="flex flex-wrap gap-2">
                <x-ui.button>Guardar</x-ui.button>
                <x-ui.button variant="secondary">Cancelar</x-ui.button>
            </div>
        </x-ui.card>
    </div>

    <div class="mt-6 max-w-xl space-y-4">
        <x-ui.alert variant="warning" title="Ejemplo de alerta">
            Este galpón tiene más de un lote activo. La producción se registrará sobre el galpón completo.
        </x-ui.alert>

        <x-ui.input label="Documento" name="documento" placeholder="Ej. 12345678" />
    </div>
</x-layouts.admin>
