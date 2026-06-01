@props([
    'variant' => 'neutral',
])

@php
    $classes = match ($variant) {
        'success' => 'bg-green-50 text-green-800 ring-1 ring-inset ring-avicore-success/25',
        'warning' => 'bg-amber-50 text-amber-900 ring-1 ring-inset ring-avicore-warning/30',
        'danger' => 'bg-red-50 text-red-800 ring-1 ring-inset ring-avicore-danger/25',
        'info' => 'bg-blue-50 text-blue-800 ring-1 ring-inset ring-avicore-info/25',
        'primary' => 'bg-avicore-soft text-avicore-primary ring-1 ring-inset ring-avicore-primary/15',
        default => 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-500/10',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {$classes}"]) }}>
    {{ $slot }}
</span>
