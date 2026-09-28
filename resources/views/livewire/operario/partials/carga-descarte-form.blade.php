@php
    $descarteIngresado = max(0, (int) $descarteAves);
    $avesEnGalpon = (int) ($galpon?->aves_actuales ?? 0);
    $descarteHoyGalpon = (int) ($resumenGalpon['descarte_aves_hoy'] ?? 0);
    $descarteEstadoHoy = $resumenGalpon['descarte_estado_hoy'] ?? \App\Support\CapturaCeroEstado::OMISION;
    $saldoRestante = max(0, $avesEnGalpon - $descarteIngresado);
    $excedeSaldo = $descarteIngresado > $avesEnGalpon;
@endphp

<form wire:submit="guardarDescarte" class="space-y-4">
    <p class="rounded-lg border border-avicore-border bg-avicore-soft/70 px-3 py-2 text-sm text-avicore-muted">
        Aves vivas en el galpón ahora:
        <span class="font-semibold text-avicore-text">{{ number_format($avesEnGalpon, 0, ',', '.') }}</span>
        @if ($descarteHoyGalpon > 0)
            · Descartaste hoy:
            <span class="font-semibold text-avicore-text">{{ number_format($descarteHoyGalpon, 0, ',', '.') }}</span>
        @endif
    </p>

    <div>
        <x-ui.input
            label="¿Cuántas aves descartaste?"
            type="number"
            inputmode="numeric"
            pattern="[0-9]*"
            step="1"
            min="1"
            wire:model.live="descarteAves"
            placeholder="Ejemplo: 5"
            autocomplete="off"
            required
        />
        <p class="mt-2 text-sm text-avicore-muted">
            Gallinas <span class="font-medium text-avicore-text">vivas</span> que sacaste del galpón.
            No es mortalidad ni huevo descartado.
        </p>
        @error('descarteAves')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($descarteIngresado > 0)
        <div
            @class([
                'rounded-lg border px-3 py-3 text-sm',
                'border-avicore-danger/40 bg-red-50' => $excedeSaldo,
                'border-avicore-primary/25 bg-avicore-soft' => ! $excedeSaldo,
            ])
            role="status"
        >
            <p class="font-medium text-avicore-text">Confirmá antes de guardar</p>
            @if ($excedeSaldo)
                <p class="mt-1 text-avicore-danger">
                    Superás el saldo vivo del galpón ({{ number_format($avesEnGalpon, 0, ',', '.') }} aves).
                </p>
            @else
                <p class="mt-1 text-avicore-muted">
                    Registrarás <span class="font-semibold text-avicore-text">{{ number_format($descarteIngresado, 0, ',', '.') }}</span>
                    aves en descarte. Quedarían
                    <span class="font-semibold text-avicore-text">{{ number_format($saldoRestante, 0, ',', '.') }}</span>
                    aves en el galpón.
                </p>
            @endif
        </div>
    @endif

    @include('livewire.operario.partials.carga-envio-feedback')

    @include('livewire.operario.partials.carga-cero-confirmar', [
        'estadoHoy' => $descarteEstadoHoy,
        'totalesHoy' => $descarteHoyGalpon,
        'tipoLabel' => 'descarte de aves',
        'wireMethod' => 'confirmarCeroDescarte',
        'wireTarget' => 'confirmarCeroDescarte',
    ])

    <x-ui.button
        type="submit"
        class="w-full py-4 text-base"
        wire:loading.attr="disabled"
        wire:target="guardarDescarte"
        :disabled="$descarteIngresado < 1 || $excedeSaldo"
    >
        <span wire:loading.remove wire:target="guardarDescarte">{{ $cargaEnvioError ? 'Reintentar' : 'Guardar descarte de aves' }}</span>
        <span wire:loading wire:target="guardarDescarte">Guardando…</span>
    </x-ui.button>
</form>
