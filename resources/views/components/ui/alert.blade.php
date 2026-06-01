@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $classes = match ($variant) {
        'success' => 'border-avicore-success/25 bg-green-50 text-green-950',
        'warning' => 'border-avicore-warning/30 bg-amber-50 text-amber-950',
        'danger' => 'border-avicore-danger/25 bg-red-50 text-red-950',
        default => 'border-avicore-info/25 bg-blue-50 text-blue-950',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border px-4 py-3 text-sm leading-relaxed {$classes}"]) }} role="alert">
    @if ($title)
        <p class="mb-1 font-semibold">{{ $title }}</p>
    @endif
    <div>{{ $slot }}</div>
</div>
