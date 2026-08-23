@php
    $alerta = $fila['alerta_mortalidad'];
    $galpon = $fila['galpon'];
    $datos = $fila['resumen'];
@endphp

<article class="avicore-operario-kpi-panel {{ $alerta ? 'ring-2 ring-amber-400/60' : '' }}">
    <header class="avicore-operario-kpi-panel__head">
        <span class="avicore-operario-carga-tile__icon" aria-hidden="true">
            <x-ui.icon name="warehouse" class="size-5" />
        </span>
        <div class="min-w-0 flex-1">
            <h3 class="avicore-operario-kpi-panel__title truncate">{{ $galpon->nombre }}</h3>
            <p class="mt-0.5 truncate text-sm text-avicore-muted">
                {{ $galpon->granja?->nombre ?? 'Sin granja' }}
            </p>
        </div>
        @if ($alerta)
            <span class="shrink-0 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                Alerta
            </span>
        @endif
    </header>

    <div class="avicore-operario-kpi-panel__metrics">
        <div class="avicore-operario-kpi-panel__metric avicore-operario-kpi-panel__metric--outline">
            <p class="avicore-operario-kpi-panel__value">
                {{ number_format($datos['huevos_hoy'], 0, ',', '.') }}
            </p>
            <p class="avicore-operario-kpi-panel__label">Huevos hoy</p>
        </div>
        <div class="avicore-operario-kpi-panel__metric {{ $datos['muertes_hoy'] > 0 ? 'avicore-operario-kpi-panel__metric--warm avicore-operario-kpi-panel__metric--warm-alert' : '' }}">
            <p class="avicore-operario-kpi-panel__value">
                {{ number_format($datos['muertes_hoy'], 0, ',', '.') }}
            </p>
            <p class="avicore-operario-kpi-panel__label">Muertes hoy</p>
        </div>
        <div class="avicore-operario-kpi-panel__metric avicore-operario-kpi-panel__metric--alive">
            <p class="avicore-operario-kpi-panel__value">
                {{ number_format($datos['aves_actuales'], 0, ',', '.') }}
            </p>
            <p class="avicore-operario-kpi-panel__label">Aves</p>
        </div>
        <div class="avicore-operario-kpi-panel__metric {{ $alerta ? 'avicore-operario-kpi-panel__metric--warm avicore-operario-kpi-panel__metric--warm-alert' : '' }}">
            <p class="avicore-operario-kpi-panel__value">
                {{ number_format($fila['mortalidad_pct'], 2, ',', '.') }}%
            </p>
            <p class="avicore-operario-kpi-panel__label">Mortalidad acum.</p>
        </div>
    </div>
</article>
