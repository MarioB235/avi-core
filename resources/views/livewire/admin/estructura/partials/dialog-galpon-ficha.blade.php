@if ($fichaGalpon)
    @php
        $galpon = $fichaGalpon['galpon'];
        $resumen = $fichaGalpon['resumen'];
    @endphp

    <x-ui.dialog wire:model="dialogGalponFichaAbierto" title="Ficha del galpón">
        <div class="space-y-5">
            <div>
                <p class="text-lg font-semibold text-avicore-text">{{ $galpon->nombre }}</p>
                @if ($galpon->codigo)
                    <p class="text-sm text-avicore-muted">Código {{ $galpon->codigo }}</p>
                @endif
            </div>

            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-avicore-muted">Granja</dt>
                    <dd class="font-medium text-avicore-text">{{ $galpon->granja->nombre }}</dd>
                </div>
                @if ($galpon->granja->dicose)
                    <div>
                        <dt class="text-avicore-muted">DICOSE</dt>
                        <dd class="font-medium text-avicore-text">{{ $galpon->granja->dicose }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-avicore-muted">Estado operativo</dt>
                    <dd class="font-medium text-avicore-text">{{ $galpon->estado->label() }}</dd>
                </div>
                <div>
                    <dt class="text-avicore-muted">Saldo vivo</dt>
                    <dd class="font-medium text-avicore-text">{{ number_format($galpon->aves_actuales, 0, ',', '.') }} aves</dd>
                </div>
                @if ($galpon->capacidad)
                    <div>
                        <dt class="text-avicore-muted">Capacidad</dt>
                        <dd class="font-medium text-avicore-text">{{ number_format($galpon->capacidad, 0, ',', '.') }} aves</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-avicore-muted">Lotes activos</dt>
                    <dd class="font-medium text-avicore-text">{{ $fichaGalpon['lotes_activos'] }}</dd>
                </div>
            </dl>

            <p class="rounded-lg bg-avicore-soft px-3 py-2 text-sm text-avicore-muted">
                {{ $fichaGalpon['saldo_nota'] }}
            </p>

            <div>
                <p class="mb-2 text-sm font-medium text-avicore-ink">Producción de hoy (galpón)</p>
                <dl class="grid gap-2 text-sm sm:grid-cols-3">
                    <div class="rounded-lg border border-avicore-border px-3 py-2">
                        <dt class="text-avicore-muted">Huevos</dt>
                        <dd class="text-lg font-semibold text-avicore-text">{{ number_format($resumen['huevos_hoy'], 0, ',', '.') }}</dd>
                    </div>
                    <div class="rounded-lg border border-avicore-border px-3 py-2">
                        <dt class="text-avicore-muted">Muertes</dt>
                        <dd class="text-lg font-semibold text-avicore-text">{{ number_format($resumen['muertes_hoy'], 0, ',', '.') }}</dd>
                    </div>
                    <div class="rounded-lg border border-avicore-border px-3 py-2">
                        <dt class="text-avicore-muted">Descarte aves</dt>
                        <dd class="text-lg font-semibold text-avicore-text">{{ number_format($resumen['descarte_aves_hoy'], 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            @if ($fichaGalpon['lotes']->isNotEmpty())
                <div>
                    <p class="mb-2 text-sm font-medium text-avicore-ink">Historial de lotes</p>
                    <div class="max-h-48 space-y-2 overflow-y-auto">
                        @foreach ($fichaGalpon['lotes'] as $lote)
                            <div class="flex items-center justify-between gap-2 rounded-lg border border-avicore-border px-3 py-2 text-sm">
                                <div>
                                    <p class="font-medium text-avicore-text">{{ $lote->codigo }}</p>
                                    <p class="text-xs text-avicore-muted">
                                        Ingreso {{ $lote->fecha_ingreso->format('d/m/Y') }}
                                        · {{ number_format($lote->cantidad_inicial, 0, ',', '.') }} aves
                                    </p>
                                </div>
                                <x-ui.badge variant="{{ $lote->estado->permiteCargaNormal() ? 'primary' : 'neutral' }}">
                                    {{ $lote->estado->label() }}
                                </x-ui.badge>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <p class="text-xs text-avicore-muted">
                {{ number_format($fichaGalpon['registros_count'], 0, ',', '.') }} registros operativos
                · {{ number_format($fichaGalpon['vacunaciones_count'], 0, ',', '.') }} vacunaciones
            </p>

            <div class="flex justify-end">
                <x-ui.button type="button" variant="secondary" wire:click="cerrarFichaGalpon">Cerrar</x-ui.button>
            </div>
        </div>
    </x-ui.dialog>
@endif
