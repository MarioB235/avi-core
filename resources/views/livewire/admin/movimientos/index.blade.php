<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Movimientos de aves"
        subtitle="Solo supervisión — vista previa del efecto y motivo obligatorio (D07). El operario no registra movimientos desde aquí."
    />

    @if (session('status') === 'movimiento-registrado')
        <x-ui.alert variant="success" class="mb-4" title="Movimiento registrado">
            El ledger quedó actualizado según la vista previa confirmada.
        </x-ui.alert>
    @endif

    <div class="avicore-operario-home-sheet">
        <x-ui.reveal as="section" aria-label="Formulario de movimiento">
            <x-ui.section-head
                eyebrow="Supervisor"
                title="Nuevo movimiento"
                subtitle="Revisá el efecto antes de confirmar."
            />

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div class="space-y-4">
                    <x-ui.select
                        label="Tipo"
                        name="tipo"
                        wire:model.live="tipo"
                        :options="$tipoOptions"
                    />

                    @if ($tipo === 'traslado')
                        <x-ui.select
                            label="Galpón origen"
                            name="galponOrigenId"
                            wire:model.live="galponOrigenId"
                            placeholder="Elegí origen"
                            :options="$galponesOptions"
                        />
                        <x-ui.select
                            label="Galpón destino"
                            name="galponDestinoId"
                            wire:model.live="galponDestinoId"
                            placeholder="Elegí destino"
                            :options="$galponesOptions"
                        />
                        <x-ui.select
                            label="Lote"
                            name="loteId"
                            wire:model.live="loteId"
                            placeholder="Elegí lote"
                            :options="$lotesOptions"
                        />
                        <x-ui.input
                            label="Cantidad a trasladar"
                            name="cantidad"
                            type="number"
                            min="1"
                            wire:model.live.debounce.300ms="cantidad"
                        />
                    @elseif ($tipo === 'entrada')
                        <x-ui.select
                            label="Galpón"
                            name="galponId"
                            wire:model.live="galponId"
                            placeholder="Elegí galpón"
                            :options="$galponesOptions"
                        />
                        <x-ui.select
                            label="Lote"
                            name="loteId"
                            wire:model.live="loteId"
                            placeholder="Elegí lote"
                            :options="$lotesOptions"
                        />
                        <x-ui.input
                            label="Cantidad de entrada"
                            name="cantidad"
                            type="number"
                            min="1"
                            wire:model.live.debounce.300ms="cantidad"
                        />
                    @elseif ($tipo === 'ajuste')
                        <x-ui.select
                            label="Galpón"
                            name="galponId"
                            wire:model.live="galponId"
                            placeholder="Elegí galpón"
                            :options="$galponesOptions"
                        />
                        <x-ui.input
                            label="Conteo físico (aves)"
                            name="conteoFisico"
                            type="number"
                            min="0"
                            wire:model.live.debounce.300ms="conteoFisico"
                        />
                    @elseif ($tipo === 'cierre')
                        <x-ui.select
                            label="Galpón"
                            name="galponId"
                            wire:model.live="galponId"
                            placeholder="Elegí galpón"
                            :options="$galponesOptions"
                        />
                        <x-ui.select
                            label="Lote"
                            name="loteId"
                            wire:model.live="loteId"
                            placeholder="Elegí lote"
                            :options="$lotesOptions"
                        />
                        <x-ui.input
                            label="Cantidad de salida"
                            name="cantidad"
                            type="number"
                            min="1"
                            wire:model.live.debounce.300ms="cantidad"
                        />
                        <label class="flex items-center gap-2 text-sm text-avicore-muted">
                            <input type="checkbox" wire:model.live="cerrarCicloLote" class="rounded border-avicore-border">
                            Cerrar ciclo del lote (remanente completo)
                        </label>
                    @else
                        <x-ui.select
                            label="Galpón"
                            name="galponId"
                            wire:model.live="galponId"
                            placeholder="Elegí galpón"
                            :options="$galponesOptions"
                        />
                        <x-ui.select
                            label="Lote"
                            name="loteId"
                            wire:model.live="loteId"
                            placeholder="Elegí lote"
                            :options="$lotesOptions"
                        />
                        <x-ui.input
                            label="Planta / destino de faena"
                            name="destinoFaena"
                            wire:model.live.debounce.300ms="destinoFaena"
                            placeholder="Ej. Frigorífico habilitado MGAP"
                        />
                        <x-ui.input
                            label="Referencia interna (opcional)"
                            name="referenciaRemito"
                            wire:model.live.debounce.300ms="referenciaRemito"
                            placeholder="Nº remito o documento de trazabilidad"
                        />
                        <x-ui.input
                            label="Cantidad de aves"
                            name="cantidad"
                            type="number"
                            min="1"
                            wire:model.live.debounce.300ms="cantidad"
                        />
                        <label class="flex items-center gap-2 text-sm text-avicore-muted">
                            <input type="checkbox" wire:model.live="cerrarCicloLote" class="rounded border-avicore-border">
                            Cerrar ciclo del lote (remanente completo)
                        </label>
                    @endif

                    <x-ui.textarea
                        label="Motivo (obligatorio)"
                        name="motivo"
                        wire:model="motivo"
                        rows="3"
                        placeholder="Ej. Traslado por capacidad, conteo de fin de semana…"
                        :error="$errors->first('motivo')"
                    />

                    <x-ui.button
                        type="button"
                        variant="primary"
                        wire:click="abrirConfirmacion"
                    >
                        Revisar y confirmar
                    </x-ui.button>
                </div>

                <aside class="rounded-xl border border-avicore-border bg-avicore-surface p-4" aria-live="polite">
                    <h2 class="text-sm font-semibold text-avicore-ink">Vista previa del efecto</h2>
                    <ul class="mt-3 space-y-2 text-sm text-avicore-muted">
                        @foreach ($vistaPrevia['lineas'] as $linea)
                            <li>{{ $linea }}</li>
                        @endforeach
                    </ul>
                    @if ($vistaPrevia['errores'] !== [])
                        <ul class="mt-3 space-y-1 text-sm text-red-700">
                            @foreach ($vistaPrevia['errores'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </aside>
            </div>
        </x-ui.reveal>
    </div>

    <x-ui.dialog wire:model="dialogConfirmarAbierto" title="Confirmar movimiento">
        <p class="text-sm text-avicore-muted">
            Verificá el efecto y el motivo antes de registrar en el ledger.
        </p>
        <ul class="mt-3 space-y-2 text-sm">
            @foreach ($vistaPrevia['lineas'] as $linea)
                <li>{{ $linea }}</li>
            @endforeach
        </ul>
        <p class="mt-4 text-sm"><span class="font-medium">Motivo:</span> {{ $motivo }}</p>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <x-ui.button type="button" variant="secondary" wire:click="$set('dialogConfirmarAbierto', false)">
                Volver
            </x-ui.button>
            <x-ui.button type="button" wire:click="ejecutarMovimiento">
                Registrar movimiento
            </x-ui.button>
        </div>
    </x-ui.dialog>
</div>
