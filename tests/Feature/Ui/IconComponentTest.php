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
            'circle-x' => ['circle-x', ['cx="12" cy="12" r="10"', 'm15 9-6 6']],
            'mail' => ['mail', ['m22 7-8.991', 'rect']],
            'message-circle-check' => ['message-circle-check', ['m9 12 2 2 4-4']],
            'key-round' => ['key-round', ['M2.586 17.414', 'circle']],
            'shield-check' => ['shield-check', ['m9 12 2 2 4-4', 'M20 13']],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function navIconProvider(): array
    {
        return [
            'home' => ['home', ['M15 21v-8', 'M3 10a2']],
            'menu' => ['menu', ['M4 12h16', 'M4 18h16']],
            'logout' => ['logout', ['M21 12H9', 'm16 17 5-5-5-5']],
            'warehouse' => ['warehouse', ['M22 8.35V20', 'M6 18h12']],
            'users' => ['users', ['M16 21v-2', 'circle cx="9"']],
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

    /**
     * @param  list<string>  $expectedFragments
     */
    #[DataProvider('navIconProvider')]
    public function test_nav_icons_render_expected_svg_markup(string $name, array $expectedFragments): void
    {
        $html = Blade::render('<x-ui.icon name="'.$name.'" />');

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

    public function test_file_backed_icon_prefers_resources_directory(): void
    {
        $html = Blade::render('<x-ui.icon name="mail" />');

        $this->assertStringContainsString('m22 7-8.991 5.727', $html);
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
