<x-ui.dialog wire:model="dialogGranjaAbierto" :title="$editingGranjaId ? 'Editar granja' : 'Nueva granja'">
    <form wire:submit="guardarGranja" class="space-y-4">
        <x-ui.input label="Nombre" name="granjaNombre" wire:model="granjaNombre" required />

        <x-ui.input
            label="DICOSE"
            name="granjaDicose"
            wire:model="granjaDicose"
            placeholder="Nº de establecimiento habilitado"
            hint="Opcional. Debe ser único por empresa."
        />

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input label="Código interno" name="granjaCodigo" wire:model="granjaCodigo" />
            <x-ui.input label="Ubicación" name="granjaUbicacion" wire:model="granjaUbicacion" />
        </div>

        @if ($editingGranjaId)
            <label class="flex items-center gap-3 rounded-lg border border-avicore-border px-3 py-3">
                <input
                    type="checkbox"
                    wire:model="granjaActiva"
                    class="size-4 rounded border-avicore-border-strong text-avicore-primary focus:ring-avicore-primary"
                />
                <span class="text-sm text-avicore-text">Granja activa</span>
            </label>
        @endif

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-ui.button type="button" variant="secondary" wire:click="cerrarGranja">Cancelar</x-ui.button>
            <x-ui.button type="submit">Guardar</x-ui.button>
        </div>
    </form>
</x-ui.dialog>
