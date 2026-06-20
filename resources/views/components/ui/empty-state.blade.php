@props([
    'title',
    'description',
    'icon' => 'clipboard-list',
])

<div {{ $attributes->merge(['class' => 'avicore-empty-state']) }}>
    <div class="avicore-empty-state__icon">
        <x-ui.icon :name="$icon" class="size-8" />
    </div>
    <p class="text-sm font-semibold text-avicore-text">{{ $title }}</p>
    <p class="mt-2 max-w-sm text-sm leading-relaxed text-avicore-muted">{{ $description }}</p>
</div>
