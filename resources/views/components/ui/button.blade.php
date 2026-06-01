@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $base = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors duration-150 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-avicore-primary disabled:cursor-not-allowed disabled:opacity-60';

    $classes = match ($variant) {
        'secondary' => 'border border-avicore-border-strong bg-avicore-card text-avicore-text hover:bg-avicore-surface',
        'danger' => 'bg-avicore-danger text-white hover:bg-red-700',
        'ghost' => 'text-avicore-primary hover:bg-avicore-soft',
        default => 'bg-avicore-primary text-white hover:bg-avicore-secondary',
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
