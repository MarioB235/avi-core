@php
    $tabla = $graficosSemanales['tabla'] ?? [];
    $series = $graficosSemanales['series'] ?? [];
@endphp

<p class="text-xs leading-relaxed text-avicore-muted">
    «—» = sin registro ese día (no es cero confirmado). «0 (confirmado)» = carga con cero explícito en campo.
    La columna de alimento suma <strong>kg entregados</strong> (camión); no es consumo diario ni conversión alimenticia.
</p>

<div class="avicore-table-wrap mt-4 overflow-x-auto">
    <table class="avicore-table avicore-table--compact min-w-[36rem]">
        <caption class="sr-only">
            Totales de los últimos siete días operativos en el alcance filtrado
        </caption>
        <thead>
            <tr>
                <th scope="col" class="text-left">Día</th>
                <th scope="col" class="text-right">Huevos aptos</th>
                <th scope="col" class="text-right">Huevos descarte</th>
                <th scope="col" class="text-right">Muertes</th>
                <th scope="col" class="text-right">Kg entregados</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tabla as $fila)
                <tr>
                    <th scope="row" class="font-medium text-avicore-ink">{{ $fila['label'] }}</th>
                    <td class="text-right tabular-nums">{{ $fila['huevos_aptos']['display'] }}</td>
                    <td class="text-right tabular-nums">{{ $fila['huevos_descarte']['display'] }}</td>
                    <td class="text-right tabular-nums">{{ $fila['muertes']['display'] }}</td>
                    <td class="text-right tabular-nums">{{ $fila['alimento_kg']['display'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div>
        <h3 class="text-sm font-semibold text-avicore-ink">Huevos aptos</h3>
        <x-ui.card class="mt-2">
            <x-ui.line-chart :points="$series['huevos_aptos'] ?? []" />
        </x-ui.card>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-avicore-ink">Huevos de descarte</h3>
        <x-ui.card class="mt-2">
            <x-ui.line-chart :points="$series['huevos_descarte'] ?? []" />
        </x-ui.card>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-avicore-ink">Muertes</h3>
        <x-ui.card class="mt-2">
            <x-ui.line-chart :points="$series['muertes'] ?? []" />
        </x-ui.card>
    </div>
    <div>
        <h3 class="text-sm font-semibold text-avicore-ink">Alimento entregado</h3>
        <x-ui.card class="mt-2">
            <x-ui.line-chart :points="$series['alimento_kg'] ?? []" unit-label="kg" />
        </x-ui.card>
    </div>
</div>
