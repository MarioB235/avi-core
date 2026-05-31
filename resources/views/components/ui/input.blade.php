@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
    'hint' => null,
])

@php
    $hasError = $error || ($name && $errors->has($name));
    $inputClass = 'block w-full min-h-11 rounded-lg border bg-avicore-card px-3 py-2.5 text-sm text-avicore-text shadow-sm placeholder:text-avicore-muted transition-colors disabled:cursor-not-allowed disabled:bg-gray-50 disabled:opacity-70 ';
    $inputClass .= $hasError
        ? 'border-avicore-danger focus:border-avicore-danger focus:ring-2 focus:ring-avicore-danger/20'
        : 'border-avicore-border-strong focus:border-avicore-primary focus:ring-2 focus:ring-avicore-primary/20';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-avicore-text">
            {{ $label }}
        </label>
    @endif

    <input
        @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
        type="{{ $type }}"
        @if ($hasError) aria-invalid="true" @endif
        {{ $attributes->except('class')->merge(['class' => $inputClass]) }}
    />

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
