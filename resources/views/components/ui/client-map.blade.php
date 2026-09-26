@props([
    'clients' => [],
])

@php
    $normalized = collect($clients)
        ->map(fn (array $client): array => [
            'id' => (string) ($client['id'] ?? ''),
            'name' => (string) ($client['name'] ?? ''),
            'zona' => (string) ($client['zona'] ?? ''),
            'lat' => (float) ($client['lat'] ?? 0),
            'lng' => (float) ($client['lng'] ?? 0),
            'ultima_compra_fecha_label' => (string) ($client['ultima_compra_fecha_label'] ?? ''),
            'ultima_compra_cantidad_label' => (string) ($client['ultima_compra_cantidad_label'] ?? ''),
            'ultima_compra_resumen' => (string) ($client['ultima_compra_resumen'] ?? ''),
        ])
        ->filter(fn (array $client): bool => $client['id'] !== '' && $client['lat'] !== 0.0 && $client['lng'] !== 0.0)
        ->values()
        ->all();

    $count = count($normalized);
@endphp

<div
    wire:ignore
    {{ $attributes->class('avicore-client-map') }}
    data-avicore-client-map
    data-clients='@json($normalized)'
    x-data="{
        clients: @js($normalized),
        selectedId: null,
        get selectedClient() {
            return this.clients.find((client) => client.id === this.selectedId) ?? null;
        },
    }"
    @avicore-client-map-select.window="
        if ($event.target === $root) {
            selectedId = $event.detail.id;
        }
    "
>
    @if ($count > 0)
        <div class="avicore-client-map__shell">
            <div
                class="avicore-client-map__canvas"
                data-avicore-client-map-canvas
                role="application"
                aria-label="Mapa interactivo con {{ $count }} clientes de ejemplo"
            ></div>

            <div
                class="avicore-client-map-detail"
                :class="{ 'avicore-client-map-detail--active': selectedClient }"
                aria-live="polite"
            >
                <template x-if="selectedClient">
                    <article class="avicore-client-map-detail__card">
                        <p class="avicore-client-map-detail__eyebrow">Cliente seleccionado</p>
                        <h3 class="avicore-client-map-detail__name" x-text="selectedClient.name"></h3>
                        <p class="avicore-client-map-detail__zona">
                            <x-ui.icon name="map-pin" class="size-4 shrink-0" />
                            <span x-text="selectedClient.zona"></span>
                        </p>

                        <dl class="avicore-client-map-detail__stats">
                            <div>
                                <dt>Última compra</dt>
                                <dd x-text="selectedClient.ultima_compra_fecha_label"></dd>
                            </div>
                            <div>
                                <dt>Cantidad</dt>
                                <dd x-text="selectedClient.ultima_compra_cantidad_label"></dd>
                            </div>
                        </dl>
                    </article>
                </template>

                <template x-if="! selectedClient">
                    <div class="avicore-client-map-detail__placeholder">
                        <x-ui.icon name="map-pin" class="avicore-client-map-detail__placeholder-icon size-5" />
                        <p class="avicore-client-map-detail__placeholder-text">
                            Tocá un pin en el mapa para ver el detalle del cliente.
                        </p>
                    </div>
                </template>
            </div>
        </div>
    @else
        <p class="avicore-client-map__empty">Sin clientes para mostrar en el mapa.</p>
    @endif
</div>
