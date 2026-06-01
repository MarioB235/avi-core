<x-layouts.admin title="Panel · AviCore" heading="Componentes UI" subheading="Referencia visual — skill clean">
    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.kpi-card label="Producción hoy" value="12.480" hint="Huevos registrados" />
        <x-ui.kpi-card label="Muertes hoy" value="18" hint="Registros validados" />
        <x-ui.kpi-card label="Galpones activos" value="6" hint="Empresa demo" />
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Botones">
            <div class="flex flex-wrap gap-2">
                <x-ui.button>Guardar</x-ui.button>
                <x-ui.button variant="secondary">Cancelar</x-ui.button>
                <x-ui.button variant="danger">Anular</x-ui.button>
            </div>
        </x-ui.card>

        <x-ui.card title="Formulario">
            <x-ui.input label="Documento" name="documento" placeholder="Ej. 12345678" class="mt-0" />
        </x-ui.card>
    </div>

    <x-ui.alert variant="warning" class="mt-8" title="Alerta">
        Este galpón tiene más de un lote activo.
    </x-ui.alert>
</x-layouts.admin>
