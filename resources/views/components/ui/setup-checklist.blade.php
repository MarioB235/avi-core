@props([
    'items' => [],
])

<ul {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @foreach ($items as $item)
        @php
            $href = $item['href'] ?? null;
            $status = $item['status'] ?? 'Pendiente';
            $statusVariant = match ($status) {
                'Disponible', 'Listo' => 'success',
                default => 'warning',
            };
        @endphp

        <li @class([
            'avicore-setup-item',
            'avicore-setup-item--link' => filled($href),
        ])>
            @if (filled($href))
                <a href="{{ $href }}" wire:navigate class="avicore-setup-item__link">
                    <div class="avicore-setup-item__icon">
                        <x-ui.icon :name="$item['icon']" class="size-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-avicore-text">{{ $item['label'] }}</p>
                        <p class="mt-0.5 text-sm text-avicore-muted">{{ $item['description'] }}</p>
                    </div>
                    <x-ui.badge :variant="$statusVariant">{{ $status }}</x-ui.badge>
                </a>
            @else
                <div class="avicore-setup-item__icon">
                    <x-ui.icon :name="$item['icon']" class="size-5" />
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-avicore-text">{{ $item['label'] }}</p>
                    <p class="mt-0.5 text-sm text-avicore-muted">{{ $item['description'] }}</p>
                </div>
                <x-ui.badge :variant="$statusVariant">{{ $status }}</x-ui.badge>
            @endif
        </li>
    @endforeach
</ul>
