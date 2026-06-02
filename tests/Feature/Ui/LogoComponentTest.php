<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LogoComponentTest extends TestCase
{
    public function test_hero_variant_renders_brand_asset_and_wordmark(): void
    {
        $html = Blade::render('<x-ui.logo size="hero" subtitle="Gestión operativa avícola" />');

        $this->assertStringContainsString('images/brand/logo-avicore.svg', $html);
        $this->assertStringContainsString('avicore-logo--hero', $html);
        $this->assertStringContainsString('AviCore', $html);
        $this->assertStringContainsString('Gestión operativa avícola', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
    }

    public function test_auth_mobile_stacked_variant_renders_compact_layout(): void
    {
        $html = Blade::render('<x-ui.logo size="auth-mobile" stacked subtitle="Gestión operativa avícola" />');

        $this->assertStringContainsString('avicore-logo--auth-mobile', $html);
        $this->assertStringContainsString('avicore-logo--stacked', $html);
        $this->assertStringContainsString('text-3xl', $html);
    }

    public function test_isotype_only_variant_exposes_accessible_label(): void
    {
        $html = Blade::render('<x-ui.logo :showName="false" />');

        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('aria-label="AviCore"', $html);
        $this->assertStringNotContainsString('aria-hidden="true"', $html);
    }
}
