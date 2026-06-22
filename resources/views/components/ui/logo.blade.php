@props([
    'size' => 'default',
    'subtitle' => null,
    'showName' => true,
    'stacked' => false,
    'theme' => 'default',
])

@php
    $imageClass = match ($size) {
        'sm' => 'size-8',
        'auth-mobile' => 'size-20 sm:size-24',
        'hero' => 'size-24 sm:size-28 lg:size-36 xl:size-40',
        'lg' => 'size-14',
        default => 'size-10',
    };

    $nameClass = match (true) {
        $theme === 'on-primary' => 'text-base font-semibold text-white',
        $size === 'auth-mobile' => 'text-3xl font-semibold leading-tight tracking-tight text-avicore-primary sm:text-4xl',
        $size === 'hero' => 'text-4xl font-semibold leading-tight tracking-tight text-avicore-primary sm:text-5xl lg:text-6xl',
        $size === 'lg' => 'text-xl font-semibold text-avicore-primary',
        default => 'text-base font-semibold text-avicore-primary',
    };

    $subtitleClass = match (true) {
        $theme === 'on-primary' => 'text-sm text-white/70',
        $size === 'auth-mobile' => 'mt-0.5 text-sm font-medium text-avicore-primary/80 sm:text-base',
        $size === 'hero' => 'mt-1 text-sm font-medium text-avicore-primary/80 sm:text-base lg:text-lg',
        default => 'text-sm text-avicore-muted',
    };

    $imageShellClass = $theme === 'on-primary'
        ? 'rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-black/5'
        : '';

    $logoClass = match ($size) {
        'hero' => 'avicore-logo avicore-logo--hero',
        'auth-mobile' => 'avicore-logo avicore-logo--auth-mobile'.($stacked ? ' avicore-logo--stacked' : ''),
        default => 'avicore-logo',
    };

    $gapClass = match (true) {
        $stacked && $size === 'auth-mobile' => 'gap-0.5',
        $stacked => 'gap-3',
        $size === 'hero' => 'gap-4 sm:gap-5 lg:gap-6',
        default => 'gap-3',
    };

    $wrapperClass = match (true) {
        $stacked => "flex flex-col items-center text-center {$gapClass}",
        default => "flex items-center {$gapClass}",
    };

    $textWrapperClass = match (true) {
        $stacked => 'min-w-0',
        $size === 'hero' => 'flex min-w-0 flex-col justify-center text-left',
        default => 'min-w-0 text-left',
    };

    $imageDimensions = match ($size) {
        'auth-mobile' => ['width' => 96, 'height' => 96],
        'hero' => ['width' => 160, 'height' => 160],
        'lg' => ['width' => 56, 'height' => 56],
        'sm' => ['width' => 32, 'height' => 32],
        default => ['width' => 40, 'height' => 40],
    };
@endphp

<div
    {{ $attributes->merge(['class' => trim("{$logoClass} {$wrapperClass}")]) }}
    @unless($showName) role="img" aria-label="AviCore" @endunless
>
    <div @class(['shrink-0 self-center', $imageShellClass])>
        <img
            src="{{ asset('images/brand/logo-avicore.png') }}"
            alt=""
            @if($showName) aria-hidden="true" @endif
            class="object-contain {{ $imageClass }}"
            width="{{ $imageDimensions['width'] }}"
            height="{{ $imageDimensions['height'] }}"
            decoding="async"
            fetchpriority="{{ $size === 'hero' ? 'high' : 'auto' }}"
        />
    </div>

    @if ($showName)
        <div class="{{ $textWrapperClass }} avicore-logo__text">
            <p class="{{ $nameClass }}">AviCore</p>
            @if ($subtitle)
                <p class="{{ $subtitleClass }}">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
</div>
