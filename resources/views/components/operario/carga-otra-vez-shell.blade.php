@props([
    'recienGuardado' => false,
    'accionOtraVez',
    'accionCerrar',
])

@if ($recienGuardado)
    <div class="space-y-4 text-center" role="status">
        <p class="text-sm font-medium text-avicore-primary">Carga guardada.</p>
        <p class="text-sm text-avicore-text-muted">¿Querés registrar otra del mismo tipo?</p>

        <x-ui.button
            type="button"
            class="w-full py-4 text-base"
            wire:click="{{ $accionOtraVez }}"
        >
            Cargar otra vez
        </x-ui.button>

        <x-ui.button
            type="button"
            variant="ghost"
            class="w-full py-3 text-base"
            wire:click="{{ $accionCerrar }}"
        >
            Listo
        </x-ui.button>
    </div>
@else
    {{ $slot }}
@endif
