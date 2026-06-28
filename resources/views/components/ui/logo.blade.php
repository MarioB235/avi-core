@props([
    'size' => 'default',
    'subtitle' => null,
    'showName' => true,
    'stacked' => false,
    'theme' => 'default',
    'entrance' => false,
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
        $theme === 'on-photo' => 'text-base font-semibold text-white',
        $size === 'auth-mobile' => 'text-3xl font-semibold leading-tight tracking-tight text-avicore-primary sm:text-4xl',
        $size === 'hero' => 'text-4xl font-semibold leading-tight tracking-tight text-avicore-primary sm:text-5xl lg:text-6xl',
        $size === 'lg' => 'text-xl font-semibold text-avicore-primary',
        default => 'text-base font-semibold text-avicore-primary',
    };

    $subtitleClass = match (true) {
        $theme === 'on-primary' => 'text-sm text-white/70',
        $theme === 'on-photo' => 'text-sm text-white/75',
        $size === 'auth-mobile' => 'mt-0.5 text-sm font-medium text-avicore-primary/80 sm:text-base',
        $size === 'hero' => 'mt-1 text-sm font-medium text-avicore-primary/80 sm:text-base lg:text-lg',
        default => 'text-sm text-avicore-muted',
    };

    $imageShellClass = match ($theme) {
        'on-primary' => 'rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-black/5',
        'on-photo' => 'rounded-full bg-white p-2 shadow-[0_8px_24px_rgba(15,23,42,0.2)] ring-2 ring-white/90',
        default => '',
    };

    $logoClass = trim(match ($size) {
        'hero' => 'avicore-logo avicore-logo--hero',
        'auth-mobile' => 'avicore-logo avicore-logo--auth-mobile'.($stacked ? ' avicore-logo--stacked' : ''),
        default => 'avicore-logo',
    }.($entrance ? ' avicore-logo--entrance' : ''));

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
        $stacked && $size === 'auth-mobile' => 'min-w-0 w-full text-center',
        $stacked => 'min-w-0 text-center',
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

    $orbitLayout = $entrance && $showName && in_array($size, ['auth-mobile', 'hero'], true);
    $orbitFieldClass = $size === 'hero'
        ? 'avicore-logo__orbit-field avicore-logo__orbit-field--hero'
        : 'avicore-logo__orbit-field avicore-logo__orbit-field--stacked';

    $orbitReserveClass = match ($size) {
        'auth-mobile' => 'h-20 w-full sm:h-24',
        'hero' => 'w-24 shrink-0 sm:w-28 lg:w-36 xl:w-40',
        default => '',
    };
@endphp

<div
    {{ $attributes->merge(['class' => trim($orbitLayout ? "{$logoClass}" : "{$logoClass} {$wrapperClass}")]) }}
    @unless($showName) role="img" aria-label="AviCore" @endunless
>
    @if ($orbitLayout)
        <div class="{{ $orbitFieldClass }}">
            <div class="avicore-logo__orbit-anchor">
                <div
                    class="avicore-logo__orbit-reserve {{ $orbitReserveClass }}"
                    aria-hidden="true"
                ></div>

                <div class="avicore-logo__orbit-text-stage">
                    <div class="{{ $textWrapperClass }} avicore-logo__text">
                        <p class="{{ $nameClass }}">AviCore</p>
                        @if ($subtitle)
                            <p class="{{ $subtitleClass }}">{{ $subtitle }}</p>
                        @endif
                    </div>

                    <div class="avicore-logo__orbit-pivot">
                        <div class="avicore-logo__orbit-spinner">
                            <div @class(['avicore-logo__mark', $imageShellClass])>
                                <img
                                    src="{{ asset('images/brand/logo-avicore.png') }}"
                                    alt=""
                                    aria-hidden="true"
                                    class="object-contain {{ $imageClass }}"
                                    width="{{ $imageDimensions['width'] }}"
                                    height="{{ $imageDimensions['height'] }}"
                                    decoding="async"
                                    fetchpriority="{{ $size === 'hero' ? 'high' : 'auto' }}"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
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
    @endif
</div>
