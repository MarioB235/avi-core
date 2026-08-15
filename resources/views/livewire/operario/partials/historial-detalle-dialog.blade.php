@if ($detalleItem)
    <x-ui.dialog wire:model="dialogDetalleAbierto" title="Detalle del registro">
        <div class="avicore-operario-historial-detalle space-y-4">
            <dl class="avicore-operario-historial-detalle__dl">
                @foreach ($detalleItem->detalleLineas as $linea)
                    <div class="avicore-operario-historial-detalle__row">
                        <dt>{{ $linea['label'] }}</dt>
                        <dd>{{ $linea['value'] }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($detalleItem->anulado)
                <p class="avicore-operario-historial-detalle__badge" role="status">Anulado</p>
            @elseif ($detalleItem->puedeAnular && ! $mostrarFormularioAnulacion)
                <x-ui.button
                    type="button"
                    variant="danger"
                    class="w-full"
                    wire:click="mostrarAnulacion"
                >
                    Anular registro
                </x-ui.button>
            @endif

            @if ($mostrarFormularioAnulacion && $detalleItem->puedeAnular && ! $detalleItem->anulado)
                <form wire:submit="anularRegistro" class="space-y-3">
                    <x-ui.textarea
                        label="Motivo de la anulación"
                        name="motivo-anulacion"
                        wire:model="motivoAnulacion"
                        rows="3"
                        placeholder="Ej.: me equivoqué en la cantidad"
                        :error="$errors->first('motivoAnulacion')"
                        required
                    />

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <x-ui.button
                            type="button"
                            variant="secondary"
                            class="w-full sm:flex-1"
                            wire:click="cancelarAnulacion"
                        >
                            Cancelar
                        </x-ui.button>
                        <x-ui.button
                            type="submit"
                            variant="danger"
                            class="w-full sm:flex-1"
                            wire:loading.attr="disabled"
                            wire:target="anularRegistro"
                        >
                            <span wire:loading.remove wire:target="anularRegistro">Confirmar anulación</span>
                            <span wire:loading wire:target="anularRegistro">Anulando…</span>
                        </x-ui.button>
                    </div>
                </form>
            @endif
        </div>
    </x-ui.dialog>
@endif
