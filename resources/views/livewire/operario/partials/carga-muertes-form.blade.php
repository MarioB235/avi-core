<form wire:submit="guardarMuertes" class="space-y-4">
    <div>
        <x-ui.input
            label="¿Cuántas aves murieron?"
            type="number"
            inputmode="numeric"
            min="1"
            wire:model="muertes"
            placeholder="Ejemplo: 12"
            required
        />
        @error('muertes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <x-ui.button
        type="submit"
        class="w-full py-4 text-base"
        wire:loading.attr="disabled"
        wire:target="guardarMuertes"
    >
        Guardar
    </x-ui.button>
</form>
