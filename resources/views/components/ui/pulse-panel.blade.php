@props([
    'pulso',
])

@php
    $estado = $pulso['estado'] ?? 'ok';
@endphp

<div {{ $attributes->merge(['class' => "avicore-pulse-status avicore-pulse-status--{$estado}"]) }}>
    <span class="avicore-pulse-status__dot" aria-hidden="true"></span>

    <div class="min-w-0 flex-1">
        <p class="avicore-pulse-status__title">{{ $pulso['estado_label'] }}</p>
        <p class="avicore-pulse-status__hint">{{ $pulso['estado_hint'] }}</p>

        @if (($pulso['huevos_hoy'] ?? 0) > 0 || ($pulso['huevos_ayer'] ?? 0) > 0)
            <p class="avicore-pulse-status__compare mt-2 text-avicore-muted">
                {{ $pulso['delta_label'] }}
            </p>
        @endif
    </div>
</div>
