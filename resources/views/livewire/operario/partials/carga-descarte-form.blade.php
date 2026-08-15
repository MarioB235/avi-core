<form wire:submit="guardarDescarte" class="space-y-4">
    <div>
        <x-ui.input
            label="¿Cuántas aves descartaste?"
            type="number"
            inputmode="numeric"
            min="1"
            wire:model="descarteAves"
            placeholder="Ejemplo: 5"
            required
        />
        <p class="mt-2 text-sm text-avicore-muted">
            Gallinas vivas que sacaste del galpón (no murieron en el piso).
        </p>
        @error('descarteAves')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <x-ui.button
        type="submit"
        class="w-full py-4 text-base"
        wire:loading.attr="disabled"
        wire:target="guardarDescarte"
    >
        <span wire:loading.remove wire:target="guardarDescarte">Guardar</span>
        <span wire:loading wire:target="guardarDescarte">Guardando…</span>
    </x-ui.button>
</form>
