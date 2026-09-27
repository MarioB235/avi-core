<x-ui.dialog wire:model="dialogLoteTransicionAbierto" title="Cambiar estado del lote">
    <form wire:submit="guardarLoteTransicion" class="space-y-4">
        <x-ui.select
            label="Nuevo estado"
            name="loteTransicionEstado"
            wire:model="loteTransicionEstado"
            :options="$loteTransicionEstadoOptions"
            required
        />

        <x-ui.textarea
            label="Motivo"
            name="loteTransicionMotivo"
            wire:model="loteTransicionMotivo"
            rows="3"
            hint="Obligatorio. Queda registrado en el historial del lote."
            required
        />

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-ui.button type="button" variant="secondary" wire:click="cerrarTransicionLote">Cancelar</x-ui.button>
            <x-ui.button type="submit">Aplicar cambio</x-ui.button>
        </div>
    </form>
</x-ui.dialog>
