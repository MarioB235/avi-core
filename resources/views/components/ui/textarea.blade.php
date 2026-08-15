@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'hint' => null,
])

@php
    $hasError = $error || ($name && isset($errors) && $errors->has($name));
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-avicore-text">
            {{ $label }}
        </label>
    @endif

    <textarea
        @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
        @if ($hasError) aria-invalid="true" @endif
        @class([
            'avicore-input block w-full rounded-xl border bg-avicore-card px-3 py-2.5 text-sm text-avicore-text placeholder:text-avicore-muted outline-none transition-colors disabled:cursor-not-allowed disabled:bg-gray-50 disabled:opacity-70',
            'border-avicore-danger focus:border-avicore-danger focus:ring-2 focus:ring-avicore-danger/20' => $hasError,
            'border-avicore-border-strong focus:border-avicore-primary focus:ring-2 focus:ring-avicore-primary/20' => ! $hasError,
        ])
        {{ $attributes->except('class') }}
    ></textarea>

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
