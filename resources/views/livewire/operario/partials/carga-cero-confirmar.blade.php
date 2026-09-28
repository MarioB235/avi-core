@php
    $estadoHoy = $estadoHoy ?? \App\Support\CapturaCeroEstado::OMISION;
    $totalesHoy = (int) ($totalesHoy ?? 0);
@endphp

@if ($estadoHoy === \App\Support\CapturaCeroEstado::CERO_CONFIRMADO)
    <p class="rounded-lg border border-avicore-primary/25 bg-avicore-soft px-3 py-2 text-sm text-avicore-muted">
        Ya confirmaste <span class="font-semibold text-avicore-text">0</span> para hoy en este galpón.
    </p>
@elseif ($totalesHoy === 0)
    <div class="rounded-lg border border-dashed border-avicore-border bg-avicore-soft/50 px-3 py-3 text-sm">
        <p class="text-avicore-muted">
            Si no hubo {{ $tipoLabel }} hoy, podés confirmarlo sin cargar cantidades.
        </p>
        <x-ui.button
            type="button"
            variant="outline"
            class="mt-3 w-full"
            wire:click="{{ $wireMethod }}"
            wire:loading.attr="disabled"
            wire:target="{{ $wireTarget }}"
        >
            <span wire:loading.remove wire:target="{{ $wireTarget }}">Confirmar 0 hoy</span>
            <span wire:loading wire:target="{{ $wireTarget }}">Confirmando…</span>
        </x-ui.button>
    </div>
@endif
