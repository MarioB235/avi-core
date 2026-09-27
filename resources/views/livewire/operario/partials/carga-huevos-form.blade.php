@php
    $aptosIngresados = max(0, (int) $huevos);
    $descarteIngresado = max(0, (int) $huevosDescarte);
    $totalIngresado = $aptosIngresados + $descarteIngresado;
    $huevosHoyGalpon = (int) ($resumenGalpon['huevos_hoy'] ?? 0);
    $descarteHoyGalpon = (int) ($resumenGalpon['huevos_descarte_hoy'] ?? 0);
@endphp

<form wire:submit="guardarHuevos" class="space-y-4">
    @if ($huevosHoyGalpon > 0 || $descarteHoyGalpon > 0)
        <p class="rounded-lg border border-avicore-border bg-avicore-soft/70 px-3 py-2 text-sm text-avicore-muted">
            Llevás hoy en este galpón:
            <span class="font-semibold text-avicore-text">
                {{ number_format($huevosHoyGalpon, 0, ',', '.') }} aptos
            </span>
            @if ($descarteHoyGalpon > 0)
                y
                <span class="font-semibold text-avicore-text">
                    {{ number_format($descarteHoyGalpon, 0, ',', '.') }} de descarte
                </span>
            @endif
            .
        </p>
    @endif

    <div>
        <x-ui.input
            label="Huevos aptos (comerciales)"
            type="number"
            inputmode="numeric"
            pattern="[0-9]*"
            step="1"
            min="0"
            wire:model.live="huevos"
            placeholder="Ejemplo: 1250"
            autocomplete="off"
            required
        />
        @error('huevos')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <x-ui.input
            label="Huevos de descarte (rotos o sucios)"
            type="number"
            inputmode="numeric"
            pattern="[0-9]*"
            step="1"
            min="0"
            wire:model.live="huevosDescarte"
            placeholder="0 si no hubo"
            autocomplete="off"
            required
        />
        @error('huevosDescarte')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($totalIngresado > 0)
        <div class="rounded-lg border border-avicore-primary/25 bg-avicore-soft px-3 py-3 text-sm" role="status">
            <p class="font-medium text-avicore-text">Confirmá antes de guardar</p>
            <ul class="mt-2 space-y-1 text-avicore-muted">
                <li>
                    Aptos:
                    <span class="font-semibold text-avicore-text">{{ number_format($aptosIngresados, 0, ',', '.') }}</span>
                    @if ($aptosIngresados > 0)
                        <span class="text-xs">({{ $unidadesHuevo->etiquetaCompacta($aptosIngresados) }})</span>
                    @endif
                </li>
                <li>
                    Descarte:
                    <span class="font-semibold text-avicore-text">{{ number_format($descarteIngresado, 0, ',', '.') }}</span>
                </li>
                <li>
                    Total de esta carga:
                    <span class="font-semibold text-avicore-text">{{ number_format($totalIngresado, 0, ',', '.') }}</span>
                </li>
            </ul>
        </div>
    @endif

    <x-ui.button
        type="submit"
        class="w-full py-4 text-base"
        wire:loading.attr="disabled"
        wire:target="guardarHuevos"
        :disabled="$totalIngresado < 1"
    >
        <span wire:loading.remove wire:target="guardarHuevos">Guardar</span>
        <span wire:loading wire:target="guardarHuevos">Guardando…</span>
    </x-ui.button>
</form>
