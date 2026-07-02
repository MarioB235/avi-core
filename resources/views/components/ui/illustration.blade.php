@props([
    'name',
])

@php
    $markup = app(\App\Support\IllustrationSvg::class)->markup(
        $name,
        $attributes->get('class', 'avicore-ui-illustration'),
    );
@endphp

@if ($markup)
    {!! $markup !!}
@endif
