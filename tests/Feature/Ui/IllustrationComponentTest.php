<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IllustrationComponentTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function operarioIllustrationProvider(): array
    {
        return [
            'operario-ave' => ['operario-ave', ['viewBox="0 0 500 500"', 'fill="#0A592B"']],
            'operario-huevo' => ['operario-huevo', ['viewBox="0 0 500 500"', 'fill="#085F2F"']],
            'operario-reloj' => ['operario-reloj', ['viewBox="0 0 500 500"', 'fill="#095F2F"']],
            'operario-vacuna' => ['operario-vacuna', ['viewBox="0 0 500 500"', 'fill="#0F623A"']],
        ];
    }

    /**
     * @param  list<string>  $expectedFragments
     */
    #[DataProvider('operarioIllustrationProvider')]
    public function test_operario_illustrations_render_expected_svg_markup(string $name, array $expectedFragments): void
    {
        $html = Blade::render('<x-ui.illustration name="'.$name.'" />');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('avicore-ui-illustration', $html);

        foreach ($expectedFragments as $fragment) {
            $this->assertStringContainsString($fragment, $html);
        }
    }

    public function test_unknown_illustration_renders_nothing(): void
    {
        $html = Blade::render('<x-ui.illustration name="unknown-illustration" />');

        $this->assertSame('', trim($html));
    }
}
