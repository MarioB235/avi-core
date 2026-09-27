<x-ui.dialog wire:model="dialogGalponAbierto" :title="$editingGalponId ? 'Editar galpón' : 'Nuevo galpón'">
    <form wire:submit="guardarGalpon" class="space-y-4">
        @if ($galponEditBloqueaReasignacionGranja ?? false)
            <div class="space-y-1.5">
                <p class="text-sm font-medium text-avicore-text">Granja</p>
                <p class="rounded-lg border border-avicore-border bg-avicore-soft/40 px-3 py-2.5 text-sm text-avicore-text">
                    {{ $galponEditGranjaNombre }}
                </p>
                <p class="text-xs text-avicore-muted">
                    No se puede cambiar: el galpón ya tiene lotes o registros operativos.
                </p>
            </div>
        @else
            <x-ui.select
                label="Granja"
                name="galponGranjaId"
                wire:model="galponGranjaId"
                placeholder="Elegí una granja"
                :options="$granjasOptions"
                required
                :error="$errors->first('galponGranjaId')"
            />
        @endif

        <x-ui.input
            label="Nombre"
            name="galponNombre"
            wire:model="galponNombre"
            required
            :error="$errors->first('galponNombre')"
        />

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.input
                label="Código"
                name="galponCodigo"
                wire:model="galponCodigo"
                hint="Opcional. Único por granja."
                :error="$errors->first('galponCodigo')"
            />
            <x-ui.input
                label="Capacidad (aves)"
                name="galponCapacidad"
                wire:model="galponCapacidad"
                type="number"
                min="1"
                :error="$errors->first('galponCapacidad')"
            />
        </div>

        <x-ui.select
            label="Estado operativo"
            name="galponEstado"
            wire:model="galponEstado"
            :options="$galponEstadoOptions"
            required
            hint="Mantenimiento o inactivo bloquean carga; el historial se conserva."
            :error="$errors->first('galponEstado')"
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
