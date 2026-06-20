@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
])

<x-ui.card {{ $attributes->merge(['class' => 'avicore-kpi-card']) }} padding="compact">
    <div class="flex items-start gap-3">
        @if ($icon)
            <div class="avicore-kpi-card-icon">
                <x-ui.icon :name="$icon" class="size-5" />
            </div>
        @endif

        <div class="min-w-0 flex-1">
            <p class="avicore-kpi-label">{{ $label }}</p>
            <p class="avicore-kpi-value mt-1">{{ $value }}</p>
            @if ($hint)
                <p class="mt-2 text-sm text-avicore-muted">{{ $hint }}</p>
            @endif
        </div>
    </div>
</x-ui.card>
