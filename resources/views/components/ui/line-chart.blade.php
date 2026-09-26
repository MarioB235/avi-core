@props([
    'points' => [],
    'emptyLabel' => 'Sin datos en este período',
])

@php
    $normalized = collect($points)
        ->map(fn ($point): array => [
            'label' => (string) ($point['label'] ?? ''),
            'value' => max(0, (int) ($point['value'] ?? 0)),
        ])
        ->values()
        ->all();

    $count = count($normalized);
    $maxValue = $count > 0 ? max(1, ...array_column($normalized, 'value')) : 1;
    $hasData = $count > 0;

    $width = 100;
    $height = 40;
    $paddingX = 2;
    $paddingY = 4;
    $plotWidth = $width - ($paddingX * 2);
    $plotHeight = $height - ($paddingY * 2);

    $polylinePoints = [];

    foreach ($normalized as $index => $point) {
        $x = $count > 1
            ? $paddingX + ($index / ($count - 1)) * $plotWidth
            : $paddingX + ($plotWidth / 2);
        $y = $paddingY + $plotHeight - (($point['value'] / $maxValue) * $plotHeight);
        $polylinePoints[] = round($x, 2).','.round($y, 2);
    }

    $polyline = implode(' ', $polylinePoints);
    $ariaLabel = $hasData
        ? 'Gráfico de línea con '.$count.' puntos. Máximo '.number_format($maxValue, 0, ',', '.').' huevos.'
        : $emptyLabel;
@endphp

<div {{ $attributes->merge(['class' => 'avicore-line-chart']) }}>
    @if ($hasData)
        <div
            class="avicore-line-chart__plot"
            role="img"
            aria-label="{{ $ariaLabel }}"
        >
            <svg
                class="avicore-line-chart__svg"
                viewBox="0 0 {{ $width }} {{ $height }}"
                preserveAspectRatio="none"
                aria-hidden="true"
            >
                <polyline
                    class="avicore-line-chart__line"
                    fill="none"
                    points="{{ $polyline }}"
                />
                @foreach ($normalized as $index => $point)
                    @php
                        $x = $count > 1
                            ? $paddingX + ($index / ($count - 1)) * $plotWidth
                            : $paddingX + ($plotWidth / 2);
                        $y = $paddingY + $plotHeight - (($point['value'] / $maxValue) * $plotHeight);
                    @endphp
                    <circle
                        class="avicore-line-chart__dot"
                        cx="{{ round($x, 2) }}"
                        cy="{{ round($y, 2) }}"
                        r="1.35"
                    />
                @endforeach
            </svg>
        </div>

        <dl class="avicore-line-chart__labels">
            @foreach ($normalized as $point)
                <div class="avicore-line-chart__label-item">
                    <dt class="sr-only">{{ $point['label'] }}</dt>
                    <dd class="avicore-line-chart__label">{{ $point['label'] }}</dd>
                </div>
            @endforeach
        </dl>
    @else
        <p class="avicore-line-chart__empty">{{ $emptyLabel }}</p>
    @endif
</div>
