<?php

namespace Tests\Feature\Ui;

use Tests\TestCase;

class PublicLayoutTest extends TestCase
{
    public function test_login_page_renders_auth_brand_shell_and_logo_entrance(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('avicore-auth-shell', false)
            ->assertSee('avicore-auth-brand', false)
            ->assertSee('avicore-auth-mobile-brand', false)
            ->assertSee('avicore-auth-brand__headline', false)
            ->assertSee('Controlá tu operación avícola en un solo lugar', false)
            ->assertSee('avicore-logo--auth-mobile', false)
            ->assertSee('avicore-logo--hero', false)
            ->assertSee('avicore-logo--entrance', false)
            ->assertSee('avicore-logo__orbit-spinner', false)
            ->assertSee('avicore-logo__orbit-text-stage', false)
            ->assertSee('avicore-brand-background', false)
            ->assertSee('Gestión operativa avícola', false);
    }
}
