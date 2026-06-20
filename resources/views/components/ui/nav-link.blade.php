@props([
    'href' => null,
    'active' => false,
    'disabled' => false,
    'icon' => null,
    'badge' => null,
])

@php
    $class = 'avicore-nav-link';
    if ($active) {
        $class .= ' avicore-nav-link--active';
    }
    if ($disabled) {
        $class .= ' avicore-nav-link--disabled';
    }

    $labelClass = $badge
        ? 'avicore-sidebar-label flex min-w-0 flex-1 flex-col items-start gap-1'
        : 'avicore-sidebar-label min-w-0 flex-1 truncate';
@endphp

@if ($href && ! $disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }} @if($active) aria-current="page" @endif>
        @if ($icon)
            <x-ui.icon :name="$icon" class="avicore-nav-link__icon size-5 shrink-0" />
        @endif
        @if ($badge)
            <span class="{{ $labelClass }}">
                <span>{{ $slot }}</span>
                <x-ui.badge variant="sidebar" class="avicore-nav-link__badge shrink-0">{{ $badge }}</x-ui.badge>
            </span>
        @else
            <span class="{{ $labelClass }}">{{ $slot }}</span>
        @endif
    </a>
@else
    <span {{ $attributes->merge(['class' => $class]) }} aria-disabled="true">
        @if ($icon)
            <x-ui.icon :name="$icon" class="avicore-nav-link__icon size-5 shrink-0" />
        @endif
        @if ($badge)
            <span class="{{ $labelClass }}">
                <span>{{ $slot }}</span>
                <x-ui.badge variant="sidebar" class="avicore-nav-link__badge shrink-0">{{ $badge }}</x-ui.badge>
            </span>
        @else
            <span class="{{ $labelClass }}">{{ $slot }}</span>
        @endif
    </span>
@endif
