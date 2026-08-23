<x-ui.dialog wire:model="dialogLoteEditarAbierto" title="Editar lote">
    <form wire:submit="guardarLoteEditar" class="space-y-4">
        <x-ui.input
            label="Nº lote SMA"
            name="loteCodigoSma"
            wire:model="loteCodigoSma"
            hint="Opcional."
        />

        <x-ui.input label="Línea / raza" name="loteLineaRaza" wire:model="loteLineaRaza" />

        <x-ui.select
            label="Estado"
            name="loteEstado"
            wire:model="loteEstado"
            :options="$loteEstadoOptions"
            required
        />

        <x-ui.textarea label="Observación" name="loteObservacion" wire:model="loteObservacion" rows="3" />

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-ui.button type="button" variant="secondary" wire:click="cerrarLoteEditar">Cancelar</x-ui.button>
            <x-ui.button type="submit">Guardar</x-ui.button>
        </div>
    </form>
</x-ui.dialog>
