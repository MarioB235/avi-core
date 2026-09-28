@php
    use App\Enums\VacunaTipo;

    $loteSeleccionado = $lotesActivos->firstWhere('id', (int) $loteId);
    $vacunaSeleccionada = $vacuna !== '' ? VacunaTipo::tryFrom($vacuna) : null;
    $vacunacionesHoy = (int) ($resumenGalpon['vacunaciones_hoy'] ?? 0);
    $formularioCompleto = $loteSeleccionado !== null && $vacunaSeleccionada !== null;
@endphp

<form wire:submit="guardarVacunacion" class="space-y-4">
    @if ($lotesActivos->isEmpty())
        <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900" role="status">
            No hay lotes activos en este galpón. Elegí otro galpón o pedí ayuda al encargado.
        </p>
    @else
        <p class="rounded-lg border border-avicore-border bg-avicore-soft/70 px-3 py-2 text-sm text-avicore-muted">
            Registrá la vacuna que <span class="font-medium text-avicore-text">aplicaste hoy</span> al lote.
            No hay calendario ni receta automática: solo el hecho registrado.
            @if ($vacunacionesHoy > 0)
                · Vacunaciones hoy:
                <span class="font-semibold text-avicore-text">{{ number_format($vacunacionesHoy, 0, ',', '.') }}</span>
            @endif
        </p>

        @php
            $loteOptions = $lotesActivos->mapWithKeys(
                fn ($lote): array => [
                    $lote->id => $lote->etiquetaVacunacion(),
                ],
            )->all();
        @endphp

        <x-ui.select
            label="¿Qué lote vacunaste?"
            name="loteId"
            wire:model.live="loteId"
            placeholder="Elegí un lote"
            :options="$loteOptions"
            required
        />
        @error('loteId')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <x-ui.select
            label="¿Qué vacuna aplicaste?"
            name="vacuna"
            wire:model.live="vacuna"
            placeholder="Elegí una vacuna"
            :options="$vacunas"
            required
        />
        @error('vacuna')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div>
            <x-ui.input
                label="Observación (opcional)"
                type="text"
                wire:model="observacionVacunacion"
                placeholder="Ejemplo: vía agua, lote completo"
                maxlength="500"
            />
            @error('observacionVacunacion')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($formularioCompleto)
            <div
                class="rounded-lg border border-avicore-primary/25 bg-avicore-soft px-3 py-3 text-sm"
                role="status"
            >
                <p class="font-medium text-avicore-text">Confirmá antes de guardar</p>
                <p class="mt-1 text-avicore-muted">
                    Lote
                    <span class="font-semibold text-avicore-text">{{ $loteSeleccionado->etiquetaVacunacion() }}</span>
                    · Vacuna
                    <span class="font-semibold text-avicore-text">{{ $vacunaSeleccionada->label() }}</span>
                </p>
            </div>
        @endif

        @include('livewire.operario.partials.carga-envio-feedback')

        <x-ui.button
            type="submit"
            class="w-full py-4 text-base"
            wire:loading.attr="disabled"
            wire:target="guardarVacunacion"
            :disabled="! $formularioCompleto"
        >
            <span wire:loading.remove wire:target="guardarVacunacion">{{ $cargaEnvioError ? 'Reintentar' : 'Guardar vacunación' }}</span>
            <span wire:loading wire:target="guardarVacunacion">Guardando…</span>
        </x-ui.button>
    @endif
</form>
