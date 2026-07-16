<form wire:submit="guardarHuevos" class="space-y-4">
    <div>
        <x-ui.input
            label="¿Cuántos huevos?"
            type="number"
            inputmode="numeric"
            min="1"
            wire:model="huevos"
            placeholder="Ejemplo: 1250"
            required
        />
        @error('huevos')
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
