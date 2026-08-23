@props([
    'label',
    'badge',
    'icon',
    'href' => null,
    'disabled' => false,
])

@if (filled($href) && ! $disabled)
    <a
        href="{{ $href }}"
        wire:navigate
        {{ $attributes->merge(['class' => 'avicore-operario-carga-tile avicore-operario-carga-tile--action']) }}
    >
        <span class="avicore-operario-carga-tile__icon" aria-hidden="true">
            <x-ui.icon :name="$icon" class="size-5" />
        </span>
        <span class="avicore-operario-carga-tile__label">{{ $label }}</span>
        <span class="avicore-operario-carga-tile__badge">{{ $badge }}</span>
    </a>
@else
    <div
        {{ $attributes->merge(['class' => 'avicore-operario-carga-tile avicore-operario-carga-tile--soon']) }}
        @if ($disabled) aria-disabled="true" @endif
    >
        <span class="avicore-operario-carga-tile__icon" aria-hidden="true">
            <x-ui.icon :name="$icon" class="size-5" />
        </span>
        <span class="avicore-operario-carga-tile__label">{{ $label }}</span>
        <span class="avicore-operario-carga-tile__badge">{{ $badge }}</span>
    </div>
@endif
