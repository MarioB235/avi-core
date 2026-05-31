@props([
    'variant' => 'neutral',
])

@php
    $classes = match ($variant) {
        'success' => 'bg-green-100 text-green-800 ring-1 ring-inset ring-green-600/20',
        'warning' => 'bg-amber-100 text-amber-900 ring-1 ring-inset ring-amber-600/20',
        'danger' => 'bg-red-100 text-red-800 ring-1 ring-inset ring-red-600/20',
        'info' => 'bg-blue-100 text-blue-800 ring-1 ring-inset ring-blue-600/20',
        'primary' => 'bg-avicore-soft text-avicore-primary ring-1 ring-inset ring-avicore-primary/15',
        default => 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-500/10',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {$classes}"]) }}>
    {{ $slot }}
</span>
