@php
    $tabs = [
        [
            'route' => 'operario.home',
            'patterns' => ['operario.home'],
            'label' => 'Inicio',
            'icon' => 'home',
        ],
        [
            'route' => 'operario.galpon',
            'patterns' => ['operario.galpon'],
            'label' => 'Galpón',
            'icon' => 'warehouse',
        ],
        [
            'route' => 'operario.cargar',
            'patterns' => ['operario.cargar', 'operario.carga.*'],
            'label' => 'Cargar',
            'icon' => 'clipboard-list',
        ],
        [
            'route' => 'operario.historial',
            'patterns' => ['operario.historial'],
            'label' => 'Historial',
            'icon' => 'clock',
        ],
    ];
@endphp

<nav
    class="avicore-operario-tab-bar"
    aria-label="Navegación operario"
>
    <div class="avicore-operario-tab-bar__inner">
        @foreach ($tabs as $tab)
            @php
                $isActive = collect($tab['patterns'])->contains(
                    fn (string $pattern) => request()->routeIs($pattern)
                );
            @endphp
            <a
                href="{{ route($tab['route']) }}"
                wire:navigate
                @class([
                    'avicore-operario-tab-bar__item',
                    'avicore-operario-tab-bar__item--active' => $isActive,
                ])
                @if ($isActive) aria-current="page" @endif
            >
                <x-ui.icon :name="$tab['icon']" class="avicore-operario-tab-bar__icon size-5" />
                <span class="avicore-operario-tab-bar__label">{{ $tab['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
