@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
    'hint' => null,
    'icon' => null,
    'togglePassword' => false,
])

@php
    $hasError = $error || ($name && $errors->has($name));
    $inputType = $type;

    $inputModifiers = trim(collect([
        $icon ? 'avicore-input--leading-icon' : null,
        $togglePassword ? 'avicore-input--trailing-action' : null,
        ! $icon && ! $togglePassword ? 'avicore-input--plain' : null,
    ])->filter()->implode(' '));
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-avicore-text">
            {{ $label }}
        </label>
    @endif

    <div class="relative" @if($togglePassword) x-data="{ show: false }" @endif>
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex w-10 items-center justify-center text-avicore-muted">
                <x-ui.icon :name="$icon" class="size-5" />
            </span>
        @endif

        <input
            @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
            @if ($togglePassword) x-bind:type="show ? 'text' : 'password'" @else type="{{ $inputType }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            @class([
                'avicore-input block w-full min-h-11 rounded-lg border bg-avicore-card py-2.5 text-sm text-avicore-text placeholder:text-avicore-muted outline-none transition-colors disabled:cursor-not-allowed disabled:bg-gray-50 disabled:opacity-70',
                $inputModifiers,
                'avicore-password-input' => $togglePassword,
                'border-avicore-danger focus:border-avicore-danger' => $hasError,
                'border-avicore-border-strong focus:border-avicore-primary' => ! $hasError,
            ])
            {{ $attributes->except('class') }}
        />

        @if ($togglePassword)
            <button
                type="button"
                class="absolute inset-y-0 right-0 z-10 flex w-10 items-center justify-center text-avicore-muted hover:text-avicore-text focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-avicore-primary"
                x-on:click="show = !show"
                x-bind:aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                x-bind:aria-pressed="show ? 'true' : 'false'"
            >
                <span class="flex items-center justify-center" x-show="! show" x-cloak>
                    <x-ui.icon name="eye" class="size-5" />
                </span>
                <span class="flex items-center justify-center" x-show="show" x-cloak>
                    <x-ui.icon name="eye-off" class="size-5" />
                </span>
            </button>
        @endif
    </div>

    @if ($hint && ! $hasError)
        <p class="text-xs text-avicore-muted">{{ $hint }}</p>
    @endif

    @if ($error)
        <p class="text-sm text-avicore-danger" role="alert">{{ $error }}</p>
    @elseif ($name)
        @error($name)
            <p class="text-sm text-avicore-danger" role="alert">{{ $message }}</p>
        @enderror
    @endif
</div>
