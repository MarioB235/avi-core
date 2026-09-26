@props([
    'banner',
])

@if (filled($banner))
    <div
        class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 lg:px-6"
        role="status"
        aria-live="polite"
    >
        <div class="mx-auto flex max-w-6xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="font-semibold">Modo soporte — {{ $banner['empresa_nombre'] }}</p>
                <p class="mt-0.5 text-amber-900/90">
                    Motivo: {{ $banner['motivo'] }}
                    · Caduca a las {{ $banner['expires_label'] }}
                    · Solo lectura operativa
                </p>
            </div>

            <form method="POST" action="{{ $banner['salir_route'] }}" class="shrink-0">
                @csrf
                <x-ui.button type="submit" variant="secondary" class="w-full sm:w-auto">
                    Salir de soporte
                </x-ui.button>
            </form>
        </div>
    </div>
@endif
