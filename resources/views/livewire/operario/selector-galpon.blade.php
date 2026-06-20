<div class="space-y-4">
    @if ($galpones->isEmpty())
        <x-ui.empty-state
            title="Sin galpones disponibles"
            description="Tu empresa aún no tiene galpones activos para carga."
        />
    @else
        <form wire:submit="guardar" class="space-y-4">
            <fieldset class="space-y-2">
                @foreach ($galpones as $galpon)
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-avicore-border bg-avicore-card px-4 py-3 has-checked:border-avicore-primary has-checked:ring-1 has-checked:ring-avicore-primary">
                        <input
                            type="radio"
                            wire:model="galponId"
                            value="{{ $galpon->id }}"
                            class="mt-1 text-avicore-primary focus:ring-avicore-primary"
                        >
                        <span>
                            <span class="block text-sm font-medium text-avicore-text">{{ $galpon->displayName() }}</span>
                            <span class="block text-xs text-avicore-muted">{{ $galpon->granja?->nombre }}</span>
                        </span>
                    </label>
                @endforeach
            </fieldset>

            @error('galponId')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <x-ui.button type="submit" class="w-full py-4 text-base" wire:loading.attr="disabled">
                Confirmar galpón
            </x-ui.button>
        </form>
    @endif
</div>
