@props([
    'title' => null,
    'padding' => 'default',
])

@php
    $paddingClass = match ($padding) {
        'compact' => 'p-4',
        'none' => 'p-0',
        default => 'p-5 sm:p-6',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-xl border border-avicore-border bg-avicore-card shadow-avicore-card {$paddingClass}"]) }}>
    @if ($title)
        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-avicore-muted">{{ $title }}</h3>
    @endif

    {{ $slot }}
</div>
