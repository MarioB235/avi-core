<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BadgeComponentTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function variantProvider(): array
    {
        return [
            'success' => ['success', 'ring-avicore-success/25'],
            'warning' => ['warning', 'ring-avicore-warning/30'],
            'danger' => ['danger', 'ring-avicore-danger/25'],
            'info' => ['info', 'ring-avicore-info/25'],
            'primary' => ['primary', 'bg-avicore-soft'],
            'sidebar' => ['sidebar', 'bg-white/15'],
            'neutral' => ['neutral', 'bg-gray-100'],
        ];
    }

    #[DataProvider('variantProvider')]
    public function test_renders_semantic_variant_classes(string $variant, string $expectedClassFragment): void
    {
        $html = Blade::render('<x-ui.badge variant="'.$variant.'">Etiqueta</x-ui.badge>');

        $this->assertStringContainsString($expectedClassFragment, $html);
        $this->assertStringContainsString('Etiqueta', $html);
        $this->assertStringContainsString('rounded-full', $html);
    }
}
