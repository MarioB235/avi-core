@props([
    'ariaLabel' => 'Navegación',
    'tabs' => [],
])

@php
    $tabCount = count($tabs);
@endphp

<nav {{ $attributes->class(['avicore-operario-tab-bar', 'lg:hidden']) }} aria-label="{{ $ariaLabel }}">
    <div class="avicore-operario-tab-bar__surface">
        <div
            class="avicore-operario-tab-bar__inner"
            style="--avicore-tab-cols: {{ $tabCount }}"
        >
            @foreach ($tabs as $tab)
                <a
                    href="{{ $tab['href'] }}"
                    wire:navigate
                    @class([
                        'avicore-operario-tab-bar__item',
                        'avicore-operario-tab-bar__item--active' => $tab['active'],
                    ])
                    @if ($tab['active']) aria-current="page" @endif
                >
                    <span class="avicore-operario-tab-bar__icon-wrap">
                        <x-ui.icon
                            :name="$tab['icon']"
                            @class([
                                'size-5',
                                'size-[1.3rem]' => $tab['active'],
                            ])
                        />
                    </span>
                    <span class="avicore-operario-tab-bar__label">{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</nav>
