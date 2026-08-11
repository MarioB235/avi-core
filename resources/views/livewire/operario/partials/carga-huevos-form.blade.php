<x-operario.carga-otra-vez-shell
    :recien-guardado="$huevosRecienGuardados"
    accion-otra-vez="cargarOtraVezHuevos"
    accion-cerrar="cerrarDialogoHuevos"
>
    <form wire:submit="guardarHuevos" class="space-y-4">
        <div>
            <x-ui.input
                label="Huevos aptos (comerciales)"
                type="number"
                inputmode="numeric"
                min="0"
                wire:model="huevos"
                placeholder="Ejemplo: 1250"
                required
            />
            @error('huevos')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.input
                label="Huevos de descarte (rotos o sucios)"
                type="number"
                inputmode="numeric"
                min="0"
                wire:model="huevosDescarte"
                placeholder="0 si no hubo"
                required
            />
            @error('huevosDescarte')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.button
            type="submit"
            class="w-full py-4 text-base"
            wire:loading.attr="disabled"
            wire:target="guardarHuevos"
        >
            <span wire:loading.remove wire:target="guardarHuevos">Guardar</span>
            <span wire:loading wire:target="guardarHuevos">Guardando…</span>
        </x-ui.button>
    </form>
</x-operario.carga-otra-vez-shell>
