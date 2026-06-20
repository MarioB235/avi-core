<div class="space-y-4">
    <x-ui.card>
        <p class="text-sm text-avicore-muted">Galpón</p>
        <p class="text-base font-medium text-avicore-text">{{ $galpon?->displayName() }}</p>
    </x-ui.card>

    <form wire:submit="guardar" class="space-y-4">
        <div>
            <x-ui.input
                label="Cantidad de huevos"
                type="number"
                inputmode="numeric"
                min="1"
                wire:model="huevos"
                placeholder="Ej: 1250"
                required
            />
            @error('huevos')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.input
                label="Observación (opcional)"
                wire:model="observacion"
                placeholder="Ej: caja rota en maple 3"
            />
            @error('observacion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <p class="text-xs text-avicore-muted">
            La fecha y hora se registran automáticamente al guardar.
        </p>

        <x-ui.button type="submit" class="w-full py-4 text-base" wire:loading.attr="disabled">
            Guardar carga
        </x-ui.button>
    </form>

    <x-ui.button href="{{ route('operario.cargar') }}" variant="secondary" class="w-full">
        Volver
    </x-ui.button>
</div>
