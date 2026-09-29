@props([
    'excepciones' => [],
])

@if ($excepciones !== [])
    <div
        class="avicore-pulse-exceptions mt-4 rounded-xl border border-amber-200/80 bg-amber-50/60 p-4 dark:border-amber-900/50 dark:bg-amber-950/30"
        role="region"
        aria-label="Qué revisar primero"
    >
        <h3 class="text-sm font-semibold text-avicore-ink">Qué revisar primero</h3>
        <p class="mt-1 text-xs text-avicore-muted">
            Excepciones del día con acceso directo al galpón en Resumen.
        </p>

        <ul class="avicore-pulse-list mt-3" role="list">
            @foreach ($excepciones as $item)
                <li
                    class="avicore-pulse-list__item {{ $item['tipo'] === 'mortalidad_referencia' ? 'avicore-pulse-list__item--alert' : '' }}"
                    role="listitem"
                >
                    <div class="min-w-0 flex-1">
                        <p class="avicore-pulse-list__name">{{ $item['titulo'] }}</p>
                        <p class="avicore-pulse-list__meta">{{ $item['detalle'] }}</p>
                    </div>
                    <a
                        href="{{ $item['accion_url'] }}"
                        class="avicore-pulse-exceptions__action shrink-0 text-sm font-medium text-avicore-primary no-underline md:hover:underline"
                        wire:navigate
                    >
                        {{ $item['accion_label'] }} →
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
