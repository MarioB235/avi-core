@props([
    'label',
    'value',
    'hint' => null,
])

<x-ui.card {{ $attributes->merge(['class' => 'space-y-1']) }} padding="compact">
    <p class="text-xs font-medium uppercase tracking-wide text-avicore-muted">{{ $label }}</p>
    <p class="text-2xl font-semibold tabular-nums tracking-tight text-avicore-text">{{ $value }}</p>
    @if ($hint)
        <p class="pt-1 text-sm text-avicore-muted">{{ $hint }}</p>
    @endif
</x-ui.card>
