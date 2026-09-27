@php
    $muertesIngresadas = max(0, (int) $muertes);
    $avesEnGalpon = (int) ($galpon?->aves_actuales ?? 0);
    $muertesHoyGalpon = (int) ($resumenGalpon['muertes_hoy'] ?? 0);
    $saldoRestante = max(0, $avesEnGalpon - $muertesIngresadas);
    $excedeSaldo = $muertesIngresadas > $avesEnGalpon;
@endphp

<form wire:submit="guardarMuertes" class="space-y-4">
    <p class="rounded-lg border border-avicore-border bg-avicore-soft/70 px-3 py-2 text-sm text-avicore-muted">
        Aves vivas en el galpón ahora:
        <span class="font-semibold text-avicore-text">{{ number_format($avesEnGalpon, 0, ',', '.') }}</span>
        @if ($muertesHoyGalpon > 0)
            · Murieron hoy:
            <span class="font-semibold text-avicore-text">{{ number_format($muertesHoyGalpon, 0, ',', '.') }}</span>
        @endif
    </p>

    <div>
        <x-ui.input
            label="¿Cuántas aves murieron?"
            type="number"
            inputmode="numeric"
            pattern="[0-9]*"
            step="1"
            min="1"
            wire:model.live="muertes"
            placeholder="Ejemplo: 12"
            autocomplete="off"
            required
        />
        @error('muertes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($muertesIngresadas > 0)
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
                    Registrarás <span class="font-semibold text-avicore-text">{{ number_format($muertesIngresadas, 0, ',', '.') }}</span>
                    muertes. Quedarían
                    <span class="font-semibold text-avicore-text">{{ number_format($saldoRestante, 0, ',', '.') }}</span>
                    aves en el galpón.
                </p>
            @endif
        </div>
    @endif

    <x-ui.button
        type="submit"
        class="w-full py-4 text-base"
        wire:loading.attr="disabled"
        wire:target="guardarMuertes"
        :disabled="$muertesIngresadas < 1 || $excedeSaldo"
    >
        <span wire:loading.remove wire:target="guardarMuertes">Guardar</span>
        <span wire:loading wire:target="guardarMuertes">Guardando…</span>
    </x-ui.button>
</form>
