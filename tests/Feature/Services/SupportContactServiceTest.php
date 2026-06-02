<?php

namespace Tests\Feature\Services;

use App\Services\SupportContactService;
use Tests\TestCase;

class SupportContactServiceTest extends TestCase
{
    public function test_builds_whatsapp_and_mailto_urls_from_config(): void
    {
        config([
            'avicore.support.whatsapp' => '+5491123456789',
            'avicore.support.whatsapp_display' => '+54 9 11 2345-6789',
            'avicore.support.email' => 'soporte@avicore.com',
        ]);

        $contacts = app(SupportContactService::class)->contacts();

        $this->assertTrue($contacts['has_whatsapp']);
        $this->assertTrue($contacts['has_email']);
        $this->assertStringContainsString('wa.me/5491123456789', $contacts['whatsapp_url']);
        $this->assertStringContainsString('mailto:soporte@avicore.com', $contacts['mailto_url']);
        $this->assertSame('+54 9 11 2345-6789', $contacts['whatsapp_display']);
    }

    public function test_rejects_invalid_whatsapp_and_email(): void
    {
        $service = app(SupportContactService::class);

        $this->assertNull($service->whatsappUrl(''));
        $this->assertNull($service->whatsappUrl('abc'));
        $this->assertNull($service->mailtoUrl(''));
        $this->assertNull($service->mailtoUrl('not-an-email'));
    }

    public function test_contacts_hide_channels_when_config_is_invalid(): void
    {
        config([
            'avicore.support.whatsapp' => '',
            'avicore.support.whatsapp_display' => '',
            'avicore.support.email' => 'invalid',
        ]);

        $contacts = app(SupportContactService::class)->contacts();

        $this->assertFalse($contacts['has_whatsapp']);
        $this->assertFalse($contacts['has_email']);
        $this->assertNull($contacts['whatsapp_url']);
        $this->assertNull($contacts['mailto_url']);
        $this->assertFalse(app(SupportContactService::class)->hasValidContactChannel());
    }

    public function test_has_valid_contact_channel_when_at_least_one_channel_works(): void
    {
        config([
            'avicore.support.whatsapp' => '',
            'avicore.support.email' => 'soporte@avicore.com',
        ]);

        $this->assertTrue(app(SupportContactService::class)->hasValidContactChannel());
    }
}
