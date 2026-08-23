<x-ui.dialog wire:model="dialogGalponAbierto" :title="$editingGalponId ? 'Editar galpón' : 'Nuevo galpón'">
    <form wire:submit="guardarGalpon" class="space-y-4">
        <x-ui.select
            label="Granja"
            name="galponGranjaId"
            wire:model="galponGranjaId"
            placeholder="Elegí una granja"
            :options="$granjasOptions"
            required
        />

        <x-ui.input label="Nombre" name="galponNombre" wire:model="galponNombre" required />

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input label="Código" name="galponCodigo" wire:model="galponCodigo" />
            <x-ui.input label="Capacidad (aves)" name="galponCapacidad" wire:model="galponCapacidad" type="number" min="1" />
        </div>

        <x-ui.select
            label="Estado operativo"
            name="galponEstado"
            wire:model="galponEstado"
            :options="$galponEstadoOptions"
            required
        />

        @if ($editingGalponId)
            <label class="flex items-center gap-3 rounded-lg border border-avicore-border px-3 py-3">
                <input
                    type="checkbox"
                    wire:model="galponActivo"
                    class="size-4 rounded border-avicore-border-strong text-avicore-primary focus:ring-avicore-primary"
                />
                <span class="text-sm text-avicore-text">Galpón activo</span>
            </label>
        @endif

        <x-ui.textarea label="Observación" name="galponObservacion" wire:model="galponObservacion" rows="3" />

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-ui.button type="button" variant="secondary" wire:click="cerrarGalpon">Cancelar</x-ui.button>
            <x-ui.button type="submit">Guardar</x-ui.button>
        </div>
    </form>
</x-ui.dialog>
