@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
])

<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-avicore-text">{{ $label }}</label>
    @endif

    <input
        @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
        type="{{ $type }}"
        {{ $attributes->except('class')->merge([
            'class' => 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-avicore-text shadow-sm placeholder:text-avicore-muted focus:border-avicore-primary focus:outline-none focus:ring-2 focus:ring-avicore-primary/20',
        ]) }}
    />

    @if ($error)
        <p class="text-sm text-avicore-danger">{{ $error }}</p>
    @endif
</div>
