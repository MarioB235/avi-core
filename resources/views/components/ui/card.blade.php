@props([
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-avicore-card p-5 shadow-sm']) }}>
    @if ($title)
        <h3 class="mb-3 text-sm font-medium text-avicore-muted">{{ $title }}</h3>
    @endif

    {{ $slot }}
</div>
