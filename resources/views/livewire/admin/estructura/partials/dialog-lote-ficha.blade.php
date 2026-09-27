@if ($fichaLote)
    @php
        $lote = $fichaLote['lote'];
    @endphp

    <x-ui.dialog wire:model="dialogLoteFichaAbierto" title="Ficha del lote">
        <div class="space-y-5">
            <div>
                <p class="text-lg font-semibold text-avicore-text">{{ $lote->codigo }}</p>
                @if ($lote->codigo_sma)
                    <p class="text-sm text-avicore-muted">SMA {{ $lote->codigo_sma }}</p>
                @endif
            </div>

            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-avicore-muted">Galpón</dt>
                    <dd class="font-medium text-avicore-text">{{ $lote->galpon->displayName() }}</dd>
                </div>
                <div>
                    <dt class="text-avicore-muted">Granja</dt>
                    <dd class="font-medium text-avicore-text">{{ $lote->galpon->granja->nombre }}</dd>
                </div>
                <div>
                    <dt class="text-avicore-muted">Estado</dt>
                    <dd class="font-medium text-avicore-text">{{ $lote->estado->label() }}</dd>
                </div>
                <div>
                    <dt class="text-avicore-muted">Tipo de ave</dt>
                    <dd class="font-medium text-avicore-text">{{ $lote->tipo_huevo->labelUi() }}</dd>
                </div>
                <div>
                    <dt class="text-avicore-muted">Población inicial</dt>
                    <dd class="font-medium text-avicore-text">{{ number_format($lote->cantidad_inicial, 0, ',', '.') }} aves</dd>
                </div>
                <div>
                    <dt class="text-avicore-muted">Edad</dt>
                    <dd class="font-medium text-avicore-text">{{ $fichaLote['edad_semanas'] }} semanas</dd>
                </div>
                <div>
                    <dt class="text-avicore-muted">Fecha nacimiento</dt>
                    <dd class="font-medium text-avicore-text">{{ $lote->fecha_nacimiento->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-avicore-muted">Fecha ingreso</dt>
                    <dd class="font-medium text-avicore-text">{{ $lote->fecha_ingreso->format('d/m/Y') }}</dd>
                </div>
                @if ($lote->linea_raza)
                    <div class="sm:col-span-2">
                        <dt class="text-avicore-muted">Línea / raza</dt>
                        <dd class="font-medium text-avicore-text">{{ $lote->linea_raza }}</dd>
                    </div>
                @endif
            </dl>

            <p class="rounded-lg bg-avicore-soft px-3 py-2 text-sm text-avicore-muted">
                {{ $fichaLote['saldo_nota'] }}
            </p>

            @if ($fichaLote['metricas_atribuibles'] && $fichaLote['metricas'])
                <div>
                    <p class="mb-2 text-sm font-medium text-avicore-ink">Métricas de hoy (atribuibles a este lote)</p>
                    <dl class="grid gap-2 text-sm sm:grid-cols-3">
                        <div class="rounded-lg border border-avicore-border px-3 py-2">
                            <dt class="text-avicore-muted">Huevos</dt>
                            <dd class="text-lg font-semibold text-avicore-text">{{ number_format($fichaLote['metricas']['huevos_hoy'], 0, ',', '.') }}</dd>
                        </div>
                        <div class="rounded-lg border border-avicore-border px-3 py-2">
                            <dt class="text-avicore-muted">Muertes</dt>
                            <dd class="text-lg font-semibold text-avicore-text">{{ number_format($fichaLote['metricas']['muertes_hoy'], 0, ',', '.') }}</dd>
                        </div>
                        <div class="rounded-lg border border-avicore-border px-3 py-2">
                            <dt class="text-avicore-muted">Saldo galpón</dt>
                            <dd class="text-lg font-semibold text-avicore-text">{{ number_format($fichaLote['metricas']['aves_actuales'], 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                </div>
            @elseif ($fichaLote['metricas_aviso'])
                <p class="rounded-lg border border-avicore-border px-3 py-2 text-sm text-avicore-muted">
                    {{ $fichaLote['metricas_aviso'] }}
                </p>
            @endif

            @if ($fichaLote['estado_historial'] !== [])
                <div>
                    <p class="mb-2 text-sm font-medium text-avicore-ink">Historial de estados</p>
                    <div class="max-h-40 space-y-2 overflow-y-auto">
                        @foreach ($fichaLote['estado_historial'] as $entrada)
                            <div class="rounded-lg border border-avicore-border px-3 py-2 text-sm">
                                <p class="font-medium text-avicore-text">{{ $entrada['transicion'] }}</p>
                                <p class="text-xs text-avicore-muted">
                                    {{ $entrada['fecha'] }}
                                    @if ($entrada['actor'] !== '—')
                                        · {{ $entrada['actor'] }}
                                    @endif
                                </p>
                                @if ($entrada['motivo'] !== '')
                                    <p class="mt-1 text-xs text-avicore-muted">{{ $entrada['motivo'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($lote->observacion)
                <div>
                    <p class="text-sm font-medium text-avicore-ink">Observación</p>
                    <p class="text-sm text-avicore-muted">{{ $lote->observacion }}</p>
                </div>
            @endif

            <div class="flex justify-end">
                <x-ui.button type="button" variant="secondary" wire:click="cerrarFichaLote">Cerrar</x-ui.button>
            </div>
        </div>
    </x-ui.dialog>
@endif
