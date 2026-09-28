@php
    $kgIngresados = max(0, (float) \App\Support\AlimentoValidacion::parseKg($alimentoKg) ?? 0);
    $kgHoyGalpon = (float) ($resumenGalpon['alimento_kg_hoy'] ?? 0);
    $kgTotalHoy = $kgHoyGalpon + $kgIngresados;
    $maxKg = \App\Support\AlimentoValidacion::MAX_KG;
    $excedeLimite = $kgIngresados > $maxKg;
@endphp

<form wire:submit="guardarAlimento" class="space-y-4">
    <p class="rounded-lg border border-avicore-border bg-avicore-soft/70 px-3 py-2 text-sm text-avicore-muted">
        Registrá cada <span class="font-medium text-avicore-text">entrega del camión</span> (kg del remito).
        No es consumo diario: si hoy no llegó ración, no cargues nada.
        @if ($kgHoyGalpon > 0)
            · Entregado hoy:
            <span class="font-semibold text-avicore-text">{{ number_format($kgHoyGalpon, 2, ',', '.') }} kg</span>
        @endif
    </p>

    <div>
        <x-ui.input
            label="Kilos entregados"
            type="text"
            inputmode="decimal"
            wire:model.live="alimentoKg"
            placeholder="Ejemplo: 8.500,50"
            autocomplete="off"
            required
        />
        <p class="mt-2 text-sm text-avicore-muted">
            Podés usar coma decimal (ej. <span class="font-medium text-avicore-text">1250,5</span>).
            Máximo {{ number_format($maxKg, 2, ',', '.') }} kg por entrega.
        </p>
        @error('alimentoKg')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($kgIngresados > 0)
        <div
            @class([
                'rounded-lg border px-3 py-3 text-sm',
                'border-avicore-danger/40 bg-red-50' => $excedeLimite,
                'border-avicore-primary/25 bg-avicore-soft' => ! $excedeLimite,
            ])
            role="status"
        >
            <p class="font-medium text-avicore-text">Confirmá antes de guardar</p>
            @if ($excedeLimite)
                <p class="mt-1 text-avicore-danger">
                    Superás el máximo por entrega ({{ number_format($maxKg, 2, ',', '.') }} kg).
                </p>
            @else
                <p class="mt-1 text-avicore-muted">
                    Esta entrega:
                    <span class="font-semibold text-avicore-text">{{ number_format($kgIngresados, 2, ',', '.') }} kg</span>.
                    @if ($kgHoyGalpon > 0)
                        Total del día quedaría en
                        <span class="font-semibold text-avicore-text">{{ number_format($kgTotalHoy, 2, ',', '.') }} kg</span>.
                    @endif
                </p>
            @endif
        </div>
    @endif

    @include('livewire.operario.partials.carga-envio-feedback')

    <x-ui.button
        type="submit"
        class="w-full py-4 text-base"
        wire:loading.attr="disabled"
        wire:target="guardarAlimento"
        :disabled="$kgIngresados < 0.01 || $excedeLimite"
    >
        <span wire:loading.remove wire:target="guardarAlimento">{{ $cargaEnvioError ? 'Reintentar' : 'Guardar entrega' }}</span>
        <span wire:loading wire:target="guardarAlimento">Guardando…</span>
    </x-ui.button>
</form>
