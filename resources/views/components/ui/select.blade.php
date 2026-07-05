@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'hint' => null,
    'placeholder' => null,
    'options' => [],
])

@php
    $wireDirective = $attributes->wire('model');
    $wireModel = $wireDirective->value();
    $wireModelLive = $wireDirective->hasModifier('live');
    $hasError = $error || ($name && isset($errors) && $errors->has($name));
    $selectId = $name ?? $attributes->get('id');
    $isRequired = $attributes->has('required');

    $normalizedOptions = [];

    if (is_array($options) && count($options) > 0) {
        foreach ($options as $value => $optionLabel) {
            $normalizedOptions[] = [
                'value' => (string) $value,
                'label' => (string) $optionLabel,
            ];
        }
    }

    $listOptions = $placeholder !== null
        ? array_merge([['value' => '', 'label' => (string) $placeholder]], $normalizedOptions)
        : $normalizedOptions;
@endphp

<div
    {{ $attributes->only('class')->merge(['class' => 'avicore-select-field space-y-1.5']) }}
    x-data="{
        open: false,
        placement: 'below',
        listMaxHeight: '14rem',
        collisionPadding: 8,
        preferredListHeightPx: 224,
        value: @if ($wireModel)
            @if ($wireModelLive)
                @entangle($wireModel).live
            @else
                @entangle($wireModel).defer
            @endif
        @else
            @js((string) $attributes->get('value', ''))
        @endif,
        options: @js($listOptions),
        placeholder: @js($placeholder ?? 'Elegir…'),
        triggerLabel() {
            if (this.value === '' || this.value === null) {
                return this.placeholder;
            }

            const found = this.options.find((option) => String(option.value) === String(this.value));

            return found?.label ?? this.placeholder;
        },
        selectOption(optionValue) {
            this.value = optionValue;
            this.open = false;
        },
        isSelected(optionValue) {
            return String(this.value) === String(optionValue);
        },
        syncPanelPosition() {
            if (! this.open || ! this.$refs.trigger) {
                return;
            }

            const rect = this.$refs.trigger.getBoundingClientRect();
            const padding = this.collisionPadding;
            const spaceBelow = window.innerHeight - rect.bottom - padding;
            const spaceAbove = rect.top - padding;
            const openBelow = spaceBelow >= spaceAbove;

            this.placement = openBelow ? 'below' : 'above';

            const available = Math.max(openBelow ? spaceBelow : spaceAbove, 88);
            const height = Math.min(this.preferredListHeightPx, available);

            this.listMaxHeight = `${height}px`;
        },
        toggleOpen() {
            if (this.open) {
                this.open = false;

                return;
            }

            this.open = true;
            this.$nextTick(() => this.syncPanelPosition());
        },
    }"
    x-on:resize.window="if (open) syncPanelPosition()"
    @click.outside="open = false"
    @keydown.escape.window="if (open) open = false"
>
    @if ($label)
        <label @if ($selectId) for="{{ $selectId }}" @endif class="block text-sm font-medium text-avicore-text">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <button
            type="button"
            id="{{ $selectId }}"
            x-ref="trigger"
            @if ($name) name="{{ $name }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            @if ($isRequired) aria-required="true" @endif
            aria-haspopup="listbox"
            :aria-expanded="open"
            @if ($selectId) aria-controls="{{ $selectId }}-listbox" @endif
            x-on:click="toggleOpen()"
            @class([
                'avicore-select-trigger flex w-full items-center justify-between gap-2 text-left',
                'border-avicore-danger' => $hasError,
                'border-avicore-border-strong' => ! $hasError,
            ])
        >
            <span
                class="min-w-0 flex-1 truncate"
                :class="(value === '' || value === null) ? 'text-avicore-muted' : 'text-avicore-text'"
                x-text="triggerLabel()"
            ></span>
            <span class="pointer-events-none flex w-10 shrink-0 items-center justify-center text-avicore-muted" aria-hidden="true">
                <x-ui.icon
                    name="chevron-down"
                    class="size-5 transition-transform duration-200"
                    x-bind:class="open ? 'rotate-180' : ''"
                />
            </span>
        </button>

        <div
            @if ($selectId) id="{{ $selectId }}-listbox" @endif
            role="listbox"
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="avicore-select-panel"
            x-bind:class="placement === 'above' ? 'avicore-select-panel--above' : 'avicore-select-panel--below'"
        >
            <ul class="avicore-select-list" x-bind:style="{ maxHeight: listMaxHeight }">
                <template x-for="option in options" :key="option.value">
                    <li>
                        <button
                            type="button"
                            role="option"
                            class="avicore-select-option"
                            x-bind:class="{ 'avicore-select-option--active': isSelected(option.value) }"
                            x-bind:aria-selected="isSelected(option.value) ? 'true' : 'false'"
                            x-on:click="selectOption(option.value)"
                            x-text="option.label"
                        ></button>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    @if ($hint && ! $hasError)
        <p class="text-xs text-avicore-muted">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="text-sm text-avicore-danger" role="alert">{{ $error }}</p>
    @elseif ($name && isset($errors))
        @error($name)
            <p class="text-sm text-avicore-danger" role="alert">{{ $message }}</p>
        @enderror
    @endif
</div>
