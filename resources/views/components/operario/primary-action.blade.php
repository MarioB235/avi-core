@props([
    'href',
    'title' => 'Registrar producción',
    'description' => 'Cargar datos del galpón seleccionado',
])

<a
    href="{{ $href }}"
    wire:navigate
    {{ $attributes->class(['avicore-operario-primary-action']) }}
>
    <span class="avicore-operario-primary-action__icon" aria-hidden="true">
        <x-ui.icon name="plus" class="size-5" />
    </span>
    <span class="avicore-operario-primary-action__copy">
        <strong class="avicore-operario-primary-action__title">{{ $title }}</strong>
        <small class="avicore-operario-primary-action__description">{{ $description }}</small>
    </span>
    <x-ui.icon name="chevron-right" class="avicore-operario-primary-action__chevron size-5 shrink-0" aria-hidden="true" />
</a>
