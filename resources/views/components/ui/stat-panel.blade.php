@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'illustration' => null,
    'tone' => 'default',
])

@php
    $toneClass = match ($tone) {
        'aves' => 'avicore-operario-kpi-panel--aves',
        'huevos' => 'avicore-operario-kpi-panel--huevos',
        default => '',
    };
@endphp

<article {{ $attributes->merge(['class' => "avicore-operario-kpi-panel avicore-operario-kpi-panel--stat {$toneClass}"]) }}>
    <header class="avicore-operario-kpi-panel__head">
        @if ($illustration)
            <span class="avicore-operario-carga-tile__icon avicore-operario-kpi-panel__icon" aria-hidden="true">
                <x-ui.illustration :name="$illustration" />
            </span>
        @elseif ($icon)
            <span class="avicore-operario-carga-tile__icon avicore-operario-kpi-panel__icon" aria-hidden="true">
                <x-ui.icon :name="$icon" class="size-5" />
            </span>
        @endif
        <h3 class="avicore-operario-kpi-panel__title">{{ $label }}</h3>
    </header>

    <div class="avicore-operario-kpi-panel__metrics">
        <div class="avicore-operario-kpi-panel__metric avicore-operario-kpi-panel__metric--alive col-span-2">
            <p class="avicore-operario-kpi-panel__value">{{ $value }}</p>
            @if ($hint)
                <p class="avicore-operario-kpi-panel__label">{{ $hint }}</p>
            @endif
        </div>
    </div>
</article>
