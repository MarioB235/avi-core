@props([
    'as' => 'div',
    'delay' => null,
])

@php
    $revealAttributes = [
        'class' => 'avicore-reveal',
        'data-avicore-reveal' => '',
    ];

    if ($delay !== null) {
        $revealAttributes['data-reveal-delay'] = (int) $delay;
    }
@endphp

<{{ $as }}
    {{ $attributes->merge($revealAttributes) }}
>
    {{ $slot }}
</{{ $as }}>
