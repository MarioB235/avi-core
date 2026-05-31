@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $classes = match ($variant) {
        'success' => 'border-green-200 bg-green-50 text-green-900',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-950',
        'danger' => 'border-red-200 bg-red-50 text-red-950',
        default => 'border-blue-200 bg-blue-50 text-blue-950',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border px-4 py-3 text-sm {$classes}"]) }} role="alert">
    @if ($title)
        <p class="mb-1 font-semibold">{{ $title }}</p>
    @endif
    <div>{{ $slot }}</div>
</div>
