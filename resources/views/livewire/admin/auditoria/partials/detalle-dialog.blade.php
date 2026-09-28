@if ($detalle)
    <x-ui.dialog wire:model="dialogDetalleAbierto" title="Detalle del evento">
        <div class="space-y-4">
            <dl class="avicore-operario-historial-detalle__dl">
                @foreach ($detalleLineas as $linea)
                    <div class="avicore-operario-historial-detalle__row">
                        <dt>{{ $linea['label'] }}</dt>
                        <dd>{{ $linea['value'] }}</dd>
                    </div>
                @endforeach
            </dl>

            <p class="text-xs text-avicore-ink-muted">
                Solo lectura — los eventos de auditoría no se editan ni eliminan (retención D07).
            </p>
        </div>
    </x-ui.dialog>
@endif
