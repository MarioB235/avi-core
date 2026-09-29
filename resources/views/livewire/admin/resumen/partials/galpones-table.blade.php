<div class="avicore-table-wrap mt-4 hidden md:block">
    <table class="avicore-table avicore-table--compact min-w-[720px]">
        <thead>
            <tr>
                <th scope="col">Galpón</th>
                <th scope="col" class="hidden lg:table-cell">Granja</th>
                <th scope="col" class="text-right">Huevos hoy</th>
                <th scope="col" class="text-right">Descarte</th>
                <th scope="col" class="text-right">Muertes hoy</th>
                <th scope="col" class="text-right hidden lg:table-cell">Alimento kg</th>
                <th scope="col" class="text-right">Aves</th>
                <th scope="col" class="text-right">Mortalidad acum.</th>
                <th scope="col" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($filas as $fila)
                @php
                    $galpon = $fila['galpon'];
                    $datos = $fila['resumen'];
                    $alerta = $fila['alerta_mortalidad'];
                @endphp
                <tr wire:key="galpon-row-{{ $galpon->id }}" @class(['md:hover:bg-avicore-soft/60', 'bg-amber-50/60' => $alerta])>
                    <td>
                        <p class="font-medium text-avicore-text">{{ $galpon->nombre }}</p>
                        <p class="text-xs text-avicore-muted lg:hidden">{{ $galpon->granja?->nombre ?? 'Sin granja' }}</p>
                    </td>
                    <td class="hidden lg:table-cell text-avicore-muted">
                        {{ $galpon->granja?->nombre ?? 'Sin granja' }}
                    </td>
                    <td class="text-right tabular-nums">
                        {{ number_format($datos['huevos_hoy'], 0, ',', '.') }}
                    </td>
                    <td class="text-right tabular-nums {{ $datos['huevos_descarte_hoy'] > 0 ? 'text-avicore-muted' : '' }}">
                        {{ number_format($datos['huevos_descarte_hoy'], 0, ',', '.') }}
                    </td>
                    <td class="text-right tabular-nums {{ $datos['muertes_hoy'] > 0 ? 'text-avicore-danger font-medium' : '' }}">
                        {{ number_format($datos['muertes_hoy'], 0, ',', '.') }}
                    </td>
                    <td class="text-right tabular-nums hidden lg:table-cell">
                        {{ number_format($fila['alimento_kg_hoy'], 0, ',', '.') }}
                    </td>
                    <td class="text-right tabular-nums">
                        {{ number_format($datos['aves_actuales'], 0, ',', '.') }}
                    </td>
                    <td class="text-right tabular-nums {{ $alerta ? 'text-amber-800 font-medium' : '' }}">
                        {{ number_format($fila['mortalidad_pct'], 2, ',', '.') }}%
                    </td>
                    <td class="text-center">
                        @if ($alerta)
                            <x-ui.badge
                                variant="warning"
                                :title="$referenciaMortalidad['etiqueta_umbral']"
                            >
                                {{ $referenciaMortalidad['etiqueta_badge'] }}
                            </x-ui.badge>
                        @else
                            <x-ui.badge variant="success">OK</x-ui.badge>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
