@php
    use App\Support\AdminNav;

    $tabs = AdminNav::tabs();
@endphp

<nav class="avicore-operario-tab-bar lg:hidden" aria-label="Navegación panel">
    <svg
        class="avicore-operario-tab-bar__edge"
        viewBox="0 0 390 4"
        preserveAspectRatio="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <defs>
            <linearGradient id="avicoreTabBarLine" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="var(--color-avicore-secondary)" stop-opacity="1" />
                <stop offset="38%" stop-color="var(--color-avicore-secondary)" stop-opacity="0.98" />
                <stop offset="70%" stop-color="var(--color-avicore-primary)" stop-opacity="0.35" />
                <stop offset="100%" stop-color="var(--color-avicore-soft)" stop-opacity="0.5" />
            </linearGradient>
        </defs>

        <path class="avicore-operario-tab-bar__edge-line" d="M0 2 H390" />
    </svg>

    <div class="avicore-operario-tab-bar__inner">
        @foreach ($tabs as $tab)
            @php($active = AdminNav::tabIsActive($tab))

            <a
                href="{{ route($tab['route']) }}"
                wire:navigate.hover
                @class([
                    'avicore-operario-tab-bar__item',
                    'avicore-operario-tab-bar__item--active' => $active,
                ])
                @if ($active) aria-current="page" @endif
            >
                <span class="avicore-operario-tab-bar__icon-wrap">
                    <x-ui.icon
                        :name="$tab['icon']"
                        @class([
                            'size-5',
                            'size-[1.35rem]' => $active,
                        ])
                    />
                </span>
                <span class="avicore-operario-tab-bar__label">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
