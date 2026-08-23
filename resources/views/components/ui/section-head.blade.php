@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'avicore-operario-home-section__head']) }}>
    @if (filled($eyebrow))
        <p class="avicore-operario-home-section__eyebrow">{{ $eyebrow }}</p>
    @endif

    <h2 class="avicore-operario-home-section__title">{{ $title }}</h2>

    @if (filled($subtitle))
        <p class="avicore-operario-home-section__subtitle">{{ $subtitle }}</p>
    @endif

    {{ $slot }}
</div>
