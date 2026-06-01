@props([
    'size' => 'default',
    'subtitle' => null,
    'showName' => true,
])

@php
    $imageClass = match ($size) {
        'sm' => 'size-8',
        'hero' => 'size-24 sm:size-28 lg:size-36 xl:size-[9.5rem]',
        'lg' => 'size-14',
        default => 'size-10',
    };

    $nameClass = match ($size) {
        'hero' => 'text-5xl leading-none sm:text-6xl lg:text-7xl xl:text-[5.25rem]',
        'lg' => 'text-xl',
        default => 'text-base',
    };

    $subtitleClass = match ($size) {
        'hero' => 'mt-0.5 text-base font-medium text-avicore-primary/80 sm:text-lg lg:text-xl',
        default => 'text-sm text-avicore-muted',
    };

    $gapClass = match ($size) {
        'hero' => 'gap-3 sm:gap-4 lg:gap-5 xl:gap-6',
        default => 'gap-3',
    };

    $imageDimensions = match ($size) {
        'hero' => ['width' => 152, 'height' => 152],
        'lg' => ['width' => 56, 'height' => 56],
        'sm' => ['width' => 32, 'height' => 32],
        default => ['width' => 40, 'height' => 40],
    };
@endphp

<div {{ $attributes->merge(['class' => "flex items-center {$gapClass}"]) }}>
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
        <div class="min-w-0 text-left">
            <p @class([
                $nameClass,
                'avicore-brand-name',
                'avicore-brand-name--hero' => $size === 'hero',
            ])>AviCore</p>
            @if ($subtitle)
                <p class="{{ $subtitleClass }}">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
</div>
