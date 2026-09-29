@props([
    'points' => [],
    'emptyLabel' => 'Sin datos en este período',
    'unitLabel' => 'huevos',
])

@php
    $normalized = collect($points)
        ->map(function ($point): array {
            $raw = $point['value'] ?? null;
            $hasValue = $raw !== null;

            return [
                'label' => (string) ($point['label'] ?? ''),
                'value' => $hasValue ? max(0, (float) $raw) : null,
                'display' => (string) ($point['display'] ?? ($hasValue ? number_format((float) $raw, 0, ',', '.') : '—')),
                'has_value' => $hasValue,
            ];
        })
        ->values()
        ->all();

    $plotted = array_values(array_filter($normalized, fn (array $point): bool => $point['has_value']));
    $count = count($normalized);
    $maxValue = $plotted !== []
        ? max(1.0, ...array_column($plotted, 'value'))
        : 1.0;
    $hasData = $plotted !== [];

    $width = 100;
    $height = 40;
    $paddingX = 2;
    $paddingY = 4;
    $plotWidth = $width - ($paddingX * 2);
    $plotHeight = $height - ($paddingY * 2);

    $segments = [];
    $currentSegment = [];

    foreach ($normalized as $index => $point) {
        if (! $point['has_value']) {
            if ($currentSegment !== []) {
                $segments[] = $currentSegment;
                $currentSegment = [];
            }

            continue;
        }

        $x = $count > 1
            ? $paddingX + ($index / ($count - 1)) * $plotWidth
            : $paddingX + ($plotWidth / 2);
        $y = $paddingY + $plotHeight - (($point['value'] / $maxValue) * $plotHeight);

        $currentSegment[] = [
            'x' => round($x, 2),
            'y' => round($y, 2),
            'label' => $point['label'],
            'display' => $point['display'],
        ];
    }

    if ($currentSegment !== []) {
        $segments[] = $currentSegment;
    }

    $ariaLabel = $hasData
        ? 'Gráfico de línea con '.$count.' días. Máximo '.number_format($maxValue, 0, ',', '.').' '.$unitLabel.'.'
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
                @foreach ($segments as $segment)
                    <polyline
                        class="avicore-line-chart__line"
                        fill="none"
                        points="{{ collect($segment)->map(fn (array $p): string => $p['x'].','.$p['y'])->implode(' ') }}"
                    />
                    @foreach ($segment as $dot)
                        <circle
                            class="avicore-line-chart__dot"
                            cx="{{ $dot['x'] }}"
                            cy="{{ $dot['y'] }}"
                            r="1.35"
                        />
                    @endforeach
                @endforeach
            </svg>
        </div>

        <dl class="avicore-line-chart__labels">
            @foreach ($normalized as $point)
                <div class="avicore-line-chart__label-item">
                    <dt class="sr-only">{{ $point['label'] }}</dt>
                    <dd class="avicore-line-chart__label">
                        <span>{{ $point['label'] }}</span>
                        <span class="block text-[0.65rem] text-avicore-muted">{{ $point['display'] }}</span>
                    </dd>
                </div>
            @endforeach
        </dl>
    @else
        <p class="avicore-line-chart__empty">{{ $emptyLabel }}</p>
    @endif
</div>
