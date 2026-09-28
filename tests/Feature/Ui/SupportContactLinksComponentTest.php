<?php

namespace Tests\Feature\Ui;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class SupportContactLinksComponentTest extends TestCase
{
    public function test_renders_whatsapp_and_email_links_when_config_is_valid(): void
    {
        config([
            'avicore.support.whatsapp' => '+5491123456789',
            'avicore.support.whatsapp_display' => '+54 9 11 2345-6789',
            'avicore.support.email' => 'soporte@avicore.com',
        ]);

        $html = Blade::render('<x-support.contact-links />');

        $this->assertStringContainsString('wa.me/5491123456789', $html);
        $this->assertStringContainsString('mailto:soporte@avicore.com', $html);
        $this->assertStringContainsString('+54 9 11 2345-6789', $html);
    }

    public function test_renders_fallback_message_when_contact_channels_are_invalid(): void
    {
        config([
            'avicore.support.whatsapp' => '',
            'avicore.support.email' => 'invalid',
        ]);

        $html = Blade::render('<x-support.contact-links />');

        $this->assertStringContainsString('Contactá a tu administrador de empresa', $html);
        $this->assertStringNotContainsString('wa.me/', $html);
        $this->assertStringNotContainsString('mailto:', $html);
    }
}
