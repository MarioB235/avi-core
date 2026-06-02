@props([
    'name',
])

@php
    $fileMarkup = app(\App\Support\IconSvg::class)->fileMarkup($name);
@endphp

<svg
    {{ $attributes->merge(['class' => 'size-5 shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}
>
    @if ($fileMarkup)
        {!! $fileMarkup !!}
    @else
        @include('components.ui.icons.inline', ['name' => $name])
    @endif
</svg>
