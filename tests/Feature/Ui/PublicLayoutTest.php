<?php

namespace Tests\Feature\Ui;

use Tests\TestCase;

class PublicLayoutTest extends TestCase
{
    public function test_login_page_renders_mobile_auth_brand_shell(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('avicore-auth-shell', false)
            ->assertSee('avicore-auth-mobile-brand', false)
            ->assertSee('avicore-logo--auth-mobile', false)
            ->assertSee('avicore-brand-background', false)
            ->assertSee('Gestión operativa avícola', false);
    }
}
