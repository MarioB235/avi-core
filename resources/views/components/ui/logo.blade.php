@props([
    'size' => 'default',
    'subtitle' => null,
    'showName' => true,
    'stacked' => false,
])

@php
    $imageClass = match ($size) {
        'sm' => 'size-8',
        'auth-mobile' => 'size-20 sm:size-24',
        'hero' => 'size-20 sm:size-24 lg:size-32',
        'lg' => 'size-14',
        default => 'size-10',
    };

    $nameClass = match ($size) {
        'auth-mobile', 'hero' => 'text-xl font-semibold leading-tight text-avicore-primary sm:text-2xl',
        'lg' => 'text-xl font-semibold text-avicore-primary',
        default => 'text-base font-semibold text-avicore-primary',
    };

    $subtitleClass = match ($size) {
        'auth-mobile', 'hero' => 'mt-1 text-sm font-medium text-avicore-primary/80 sm:text-base',
        default => 'text-sm text-avicore-muted',
    };

    $gapClass = match (true) {
        $stacked && $size === 'auth-mobile' => 'gap-1.5',
        $stacked => 'gap-3',
        default => 'gap-3',
    };

    $wrapperClass = $stacked
        ? "flex flex-col items-center text-center {$gapClass}"
        : "flex items-center {$gapClass}";

    $imageDimensions = match ($size) {
        'auth-mobile' => ['width' => 96, 'height' => 96],
        'hero' => ['width' => 128, 'height' => 128],
        'lg' => ['width' => 56, 'height' => 56],
        'sm' => ['width' => 32, 'height' => 32],
        default => ['width' => 40, 'height' => 40],
    };
@endphp

<div {{ $attributes->merge(['class' => $wrapperClass]) }}>
    <img
        src="{{ asset('images/brand/logo-avicore.svg') }}"
        alt=""
        class="shrink-0 object-contain {{ $imageClass }}"
        width="{{ $imageDimensions['width'] }}"
        height="{{ $imageDimensions['height'] }}"
        decoding="async"
        fetchpriority="{{ $size === 'hero' ? 'high' : 'auto' }}"
    />

    @if ($showName)
        <div @class(['min-w-0', 'text-left' => ! $stacked])>
            <p class="{{ $nameClass }}">AviCore</p>
            @if ($subtitle)
                <p class="{{ $subtitleClass }}">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
</div>
