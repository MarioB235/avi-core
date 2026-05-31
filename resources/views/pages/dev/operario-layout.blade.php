<x-layouts.operario-mobile galpon="Galpón Norte · Lote 12">
    <x-ui.alert variant="info" title="Vista operario">
        Layout móvil con galpón visible, botones grandes y foco en carga rápida.
    </x-ui.alert>

    <div class="mt-4 space-y-4">
        <x-ui.card title="Carga de huevos">
            <x-ui.input label="Cantidad" name="huevos" type="number" inputmode="numeric" placeholder="0" />
            <x-ui.button class="mt-4 w-full">Guardar carga</x-ui.button>
        </x-ui.card>

        <x-ui.card title="Últimas cargas">
            <p class="text-sm text-avicore-muted">Sin registros — módulo operario pendiente.</p>
        </x-ui.card>
    </div>
</x-layouts.operario-mobile>
