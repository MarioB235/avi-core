@props([
    'href' => null,
    'active' => false,
    'disabled' => false,
])

@php
    $class = 'avicore-nav-link';
    if ($active) {
        $class .= ' avicore-nav-link--active';
    }
    if ($disabled) {
        $class .= ' avicore-nav-link--disabled';
    }
@endphp

@if ($href && ! $disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }} @if($active) aria-current="page" @endif>
        {{ $slot }}
    </a>
@else
    <span {{ $attributes->merge(['class' => $class]) }} aria-disabled="true">
        {{ $slot }}
    </span>
@endif
