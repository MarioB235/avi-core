@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $base = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-avicore-primary disabled:cursor-not-allowed disabled:opacity-60 active:scale-[0.99]';

    $classes = match ($variant) {
        'secondary' => 'border border-avicore-border-strong bg-avicore-card text-avicore-text shadow-sm hover:bg-gray-50 active:bg-gray-100',
        'danger' => 'bg-avicore-danger text-white hover:bg-red-700 active:bg-red-800',
        'ghost' => 'bg-transparent text-avicore-primary hover:bg-avicore-soft active:bg-avicore-soft/80',
        default => 'bg-avicore-primary text-white shadow-sm hover:bg-avicore-secondary active:bg-avicore-primary',
    };

    $baseClass = "{$base} {$classes}";
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
