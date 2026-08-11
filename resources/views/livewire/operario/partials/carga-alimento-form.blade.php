<x-operario.carga-otra-vez-shell
    :recien-guardado="$alimentoRecienGuardado"
    accion-otra-vez="cargarOtraVezAlimento"
    accion-cerrar="cerrarDialogoAlimento"
>
    <form wire:submit="guardarAlimento" class="space-y-4">
        <p class="text-sm leading-relaxed text-foreground/70">
            Registrá la entrega cuando llega el camión de ración (kilos del remito o ticket).
        </p>

        <div>
            <x-ui.input
                label="Kilos entregados"
                type="number"
                inputmode="decimal"
                min="0.01"
                step="0.01"
                wire:model="alimentoKg"
                placeholder="Ejemplo: 8500"
                required
            />
            @error('alimentoKg')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.button
            type="submit"
            class="w-full py-4 text-base"
            wire:loading.attr="disabled"
            wire:target="guardarAlimento"
        >
            <span wire:loading.remove wire:target="guardarAlimento">Guardar entrega</span>
            <span wire:loading wire:target="guardarAlimento">Guardando…</span>
        </x-ui.button>
    </form>
</x-operario.carga-otra-vez-shell>
