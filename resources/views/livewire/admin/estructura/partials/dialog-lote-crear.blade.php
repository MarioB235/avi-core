<x-ui.dialog wire:model="dialogLoteCrearAbierto" title="Nuevo lote">
    <form wire:submit="guardarLoteCrear" class="space-y-4">
        <x-ui.select
            label="Galpón"
            name="loteGalponId"
            wire:model="loteGalponId"
            placeholder="Elegí un galpón"
            :options="$galponesOptions"
            required
        />

        <x-ui.input
            label="Nº lote SMA"
            name="loteCodigoSma"
            wire:model="loteCodigoSma"
            hint="Opcional. Código del sistema del gobierno."
        />

        <x-ui.select
            label="Tipo de ave"
            name="loteTipoHuevo"
            wire:model="loteTipoHuevo"
            :options="$tipoHuevoOptions"
            required
        />

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input label="Cantidad de aves" name="loteCantidad" wire:model="loteCantidad" type="number" min="1" required />
            <x-ui.input label="Fecha de nacimiento" name="loteFechaNacimiento" wire:model="loteFechaNacimiento" type="date" required />
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-ui.button type="button" variant="secondary" wire:click="cerrarLoteCrear">Cancelar</x-ui.button>
            <x-ui.button type="submit">Registrar lote</x-ui.button>
        </div>
    </form>
</x-ui.dialog>
