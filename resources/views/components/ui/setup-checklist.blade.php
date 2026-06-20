@props([
    'items' => [],
])

<ul {{ $attributes->merge(['class' => 'space-y-3']) }}>
    @foreach ($items as $item)
        <li class="avicore-setup-item">
            <div class="avicore-setup-item__icon">
                <x-ui.icon :name="$item['icon']" class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-avicore-text">{{ $item['label'] }}</p>
                <p class="mt-0.5 text-sm text-avicore-muted">{{ $item['description'] }}</p>
            </div>
            <x-ui.badge variant="warning">{{ $item['status'] ?? 'Pendiente' }}</x-ui.badge>
        </li>
    @endforeach
</ul>
