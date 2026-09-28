@if ($detalleItem)
    <x-ui.dialog wire:model="dialogDetalleAbierto" title="Detalle del registro">
        <div class="space-y-4">
            <dl class="avicore-operario-historial-detalle__dl">
                @foreach ($detalleItem->detalleLineas as $linea)
                    <div class="avicore-operario-historial-detalle__row">
                        <dt>{{ $linea['label'] }}</dt>
                        <dd>{{ $linea['value'] }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($detalleItem->correccionesHistorial !== [])
                <div class="space-y-3">
                    <p class="text-sm font-medium text-avicore-ink">Historial de correcciones</p>
                    @foreach ($detalleItem->correccionesHistorial as $correccionLineas)
                        <div class="rounded-lg border border-avicore-border/60 bg-avicore-surface-muted/40 p-3">
                            <dl class="avicore-operario-historial-detalle__dl">
                                @foreach ($correccionLineas as $linea)
                                    <div class="avicore-operario-historial-detalle__row">
                                        <dt>{{ $linea['label'] }}</dt>
                                        <dd>{{ $linea['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($detalleItem->anulado)
                <p class="avicore-operario-historial-detalle__badge" role="status">
                    Anulado
                    @if ($detalleItem->motivoAnulacion)
                        — {{ $detalleItem->motivoAnulacion }}
                    @endif
                </p>
            @elseif ($detalleItem->puedeCorregir && ! $mostrarFormularioCorreccion)
                <x-ui.button
                    type="button"
                    variant="secondary"
                    class="w-full"
                    wire:click="mostrarCorreccion"
                >
                    Corregir registro
                </x-ui.button>
            @endif

            @if ($mostrarFormularioCorreccion && $detalleItem->puedeCorregir && ! $detalleItem->anulado)
                <form wire:submit="guardarCorreccion" class="space-y-3">
                    @if (in_array('huevos', $detalleItem->camposCorregibles, true))
                        <x-ui.input
                            label="Huevos aptos"
                            name="corregir-huevos"
                            type="number"
                            min="0"
                            wire:model="corregirHuevos"
                            :error="$errors->first('corregirHuevos')"
                            required
                        />
                        <x-ui.input
                            label="Huevos descarte"
                            name="corregir-huevos-descarte"
                            type="number"
                            min="0"
                            wire:model="corregirHuevosDescarte"
                            :error="$errors->first('corregirHuevos')"
                            required
                        />
                    @endif

                    @if (in_array('muertes', $detalleItem->camposCorregibles, true))
                        <x-ui.input
                            label="Muertes"
                            name="corregir-muertes"
                            type="number"
                            min="0"
                            wire:model="corregirMuertes"
                            :error="$errors->first('corregirMuertes')"
                            required
                        />
                    @endif

                    @if (in_array('descarte_aves', $detalleItem->camposCorregibles, true))
                        <x-ui.input
                            label="Descarte de aves"
                            name="corregir-descarte-aves"
                            type="number"
                            min="0"
                            wire:model="corregirDescarteAves"
                            :error="$errors->first('corregirDescarteAves')"
                            required
                        />
                    @endif

                    @if (in_array('alimento_kg', $detalleItem->camposCorregibles, true))
                        <x-ui.input
                            label="Alimento (kg)"
                            name="corregir-alimento-kg"
                            inputmode="decimal"
                            wire:model="corregirAlimentoKg"
                            :error="$errors->first('corregirAlimentoKg')"
                            required
                        />
                    @endif

                    <x-ui.date-picker
                        label="Fecha efectiva"
                        name="fechaEfectivaCorreccion"
                        wire:model="fechaEfectivaCorreccion"
                        panel-title="Fecha efectiva de la corrección"
                        :max="$fechaMaxima"
                        :error="$errors->first('fechaEfectivaCorreccion')"
                    />

                    <x-ui.textarea
                        label="Motivo de la corrección"
                        name="motivo-correccion"
                        wire:model="motivoCorreccion"
                        rows="3"
                        placeholder="Ej.: el operario cargó de más"
                        :error="$errors->first('motivoCorreccion')"
                        required
                    />

                    @if ($errors->has('correccion'))
                        <p class="text-sm text-red-600" role="alert">{{ $errors->first('correccion') }}</p>
                    @endif

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <x-ui.button
                            type="button"
                            variant="secondary"
                            class="w-full sm:flex-1"
                            wire:click="cancelarCorreccion"
                        >
                            Cancelar
                        </x-ui.button>
                        <x-ui.button
                            type="submit"
                            variant="primary"
                            class="w-full sm:flex-1"
                            wire:loading.attr="disabled"
                            wire:target="guardarCorreccion"
                        >
                            <span wire:loading.remove wire:target="guardarCorreccion">Confirmar corrección</span>
                            <span wire:loading wire:target="guardarCorreccion">Guardando…</span>
                        </x-ui.button>
                    </div>
                </form>
            @elseif (! $mostrarFormularioCorreccion)
                <p class="text-sm text-avicore-muted">
                    @if ($detalleItem->puedeCorregir)
                        Podés corregir este registro si hubo un error de carga.
                    @else
                        Vista de supervisión — solo lectura.
                    @endif
                </p>
            @endif
        </div>
    </x-ui.dialog>
@endif
