<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IconComponentTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function authIconProvider(): array
    {
        return [
            'id-card' => ['id-card', ['M16 10h2', 'rect']],
            'lock-keyhole' => ['lock-keyhole', ['M7 10V7', 'rect']],
            'eye' => ['eye', ['M2.062 12.348', 'circle']],
            'eye-off' => ['eye-off', ['m2 2 20 20']],
        ];
    }

    /**
     * @param  list<string>  $expectedFragments
     */
    #[DataProvider('authIconProvider')]
    public function test_auth_icons_render_expected_svg_markup(string $name, array $expectedFragments): void
    {
        $html = Blade::render('<x-ui.icon name="'.$name.'" />');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('stroke-width="2"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);

        foreach ($expectedFragments as $fragment) {
            $this->assertStringContainsString($fragment, $html);
        }
    }

    public function test_document_alias_renders_same_as_id_card(): void
    {
        $idCard = $this->normalizeSvgHtml(Blade::render('<x-ui.icon name="id-card" />'));
        $document = $this->normalizeSvgHtml(Blade::render('<x-ui.icon name="document" />'));

        $this->assertSame($idCard, $document);
    }

    public function test_lock_alias_renders_same_as_lock_keyhole(): void
    {
        $lockKeyhole = $this->normalizeSvgHtml(Blade::render('<x-ui.icon name="lock-keyhole" />'));
        $lock = $this->normalizeSvgHtml(Blade::render('<x-ui.icon name="lock" />'));

        $this->assertSame($lockKeyhole, $lock);
    }

    public function test_unknown_icon_renders_fallback(): void
    {
        $html = Blade::render('<x-ui.icon name="unknown-icon" />');

        $this->assertStringContainsString('<circle cx="12" cy="12" r="10"', $html);
    }

    private function normalizeSvgHtml(string $html): string
    {
        return preg_replace('/\s+/', ' ', trim($html)) ?? '';
    }
}
