@php
    use App\Support\OperarioNav;

    $tabs = OperarioNav::tabs();
@endphp

<nav class="avicore-operario-tab-bar lg:hidden" aria-label="Navegación operario">
    <div class="avicore-operario-tab-bar__surface">
        <div class="avicore-operario-tab-bar__inner">
            @foreach ($tabs as $tab)
                @php($active = OperarioNav::tabIsActive($tab))

                <a
                    href="{{ route($tab['route']) }}"
                    wire:navigate
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
                                'size-[1.3rem]' => $active,
                            ])
                        />
                    </span>
                    <span class="avicore-operario-tab-bar__label">{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</nav>
