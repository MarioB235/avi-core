<div
    class="avicore-operario-galpon-selector"
    x-data="{ open: @entangle('selectorGalponAbierto') }"
    :class="{ 'avicore-operario-galpon-selector--open': open }"
    wire:loading.class="avicore-operario-galpon-selector--loading"
    wire:target="seleccionarGalpon"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        x-on:click.stop="open = ! open"
        @class([
            'avicore-operario-home-hero__galpon',
            'avicore-operario-home-hero__galpon--empty' => $galpon === null,
        ])
        :aria-expanded="open"
        aria-haspopup="listbox"
        aria-controls="operario-galpon-listbox"
    >
        <x-ui.icon name="warehouse" class="size-4 shrink-0" />
        <span class="truncate">{{ $galponEtiqueta }}</span>
        <x-ui.icon
            name="chevron-down"
            @class([
                'size-4 shrink-0 opacity-80 transition-transform duration-200',
            ])
            x-bind:class="open ? 'rotate-180' : ''"
        />
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="avicore-operario-galpon-selector__backdrop"
        aria-hidden="true"
        x-on:click="open = false"
    ></div>

    <div
        id="operario-galpon-listbox"
        role="listbox"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="avicore-operario-galpon-selector__panel"
        x-on:click.outside="open = false"
    >
        @if ($galpones->isEmpty())
            <p class="avicore-operario-galpon-selector__empty">
                Tu empresa aún no tiene galpones activos para carga.
            </p>
        @else
            <ul class="avicore-operario-galpon-selector__list">
                @foreach ($galpones as $item)
                    <li>
                        <button
                            type="button"
                            role="option"
                            wire:click="seleccionarGalpon({{ $item->id }})"
                            wire:loading.attr="disabled"
                            x-on:click="open = false"
                            @class([
                                'avicore-operario-galpon-selector__option',
                                'avicore-operario-galpon-selector__option--active' => $galpon?->id === $item->id,
                            ])
                            @if ($galpon?->id === $item->id) aria-selected="true" @endif
                        >
                            <span class="block truncate text-sm font-semibold text-avicore-text">
                                {{ $item->displayName() }}
                            </span>
                            @if ($item->granja?->nombre)
                                <span class="block truncate text-xs text-avicore-muted">
                                    {{ $item->granja->nombre }}
                                </span>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        @error('galponId')
            <p class="avicore-operario-galpon-selector__error">{{ $message }}</p>
        @enderror
    </div>
</div>
