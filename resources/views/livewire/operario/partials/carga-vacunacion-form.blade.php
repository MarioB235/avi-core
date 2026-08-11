<x-operario.carga-otra-vez-shell
    :recien-guardado="$vacunacionRecienGuardada"
    accion-otra-vez="cargarOtraVezVacunacion"
    accion-cerrar="cerrarDialogoVacunacion"
>
    <form wire:submit="guardarVacunacion" class="space-y-4">
        @if ($lotesActivos->isEmpty())
            <p class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900" role="status">
                No hay lotes activos en este galpón. Elegí otro galpón o pedí ayuda al encargado.
            </p>
        @else
            @php
                $loteOptions = $lotesActivos->mapWithKeys(
                    fn ($lote): array => [
                        $lote->id => $lote->codigo.' · '.number_format($lote->cantidad_inicial, 0, ',', '.').' aves',
                    ],
                )->all();
            @endphp

            <x-ui.select
                label="¿Qué lote vacunaste?"
                name="loteId"
                wire:model.defer="loteId"
                placeholder="Elegí un lote"
                :options="$loteOptions"
                required
            />

            <x-ui.select
                label="¿Qué vacuna aplicaste?"
                name="vacuna"
                wire:model.defer="vacuna"
                placeholder="Elegí una vacuna"
                :options="$vacunas"
                required
            />

            <x-ui.button
                type="submit"
                class="w-full py-4 text-base !transition-none"
                wire:loading.attr="disabled"
                wire:target="guardarVacunacion"
            >
                <span wire:loading.remove wire:target="guardarVacunacion">Guardar</span>
                <span wire:loading wire:target="guardarVacunacion">Guardando…</span>
            </x-ui.button>
        @endif
    </form>
</x-operario.carga-otra-vez-shell>
