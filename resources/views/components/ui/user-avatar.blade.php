@props([
    'name',
    'size' => 'md',
    'decorative' => false,
])

@php
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $initials = collect($parts)
        ->filter()
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');

    $displayInitials = $initials !== '' ? $initials : '?';

    $sizeClass = match ($size) {
        'sm' => 'avicore-user-avatar--sm',
        'nav' => 'avicore-user-avatar--nav',
        default => 'avicore-user-avatar--md',
    };
@endphp

<div
    {{ $attributes->merge(['class' => "avicore-user-avatar {$sizeClass}"]) }}
    @if ($decorative)
        aria-hidden="true"
    @else
        role="img"
        aria-label="Avatar de {{ $name }}"
    @endif
>
    {{ $displayInitials }}
</div>
