@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-gray-300 bg-white text-avicore-text hover:bg-gray-50',
        'danger' => 'bg-avicore-danger text-white hover:bg-red-700',
        default => 'bg-avicore-primary text-white hover:bg-avicore-secondary',
    };

    $baseClass = "inline-flex min-h-11 items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-avicore-primary/30 disabled:cursor-not-allowed disabled:opacity-60 {$classes}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        {{ $slot }}
    </button>
@endif
