<?php

namespace Tests\Unit\Support;

use App\Support\ProductionSecurityConfig;
use Tests\TestCase;

class ProductionSecurityConfigTest extends TestCase
{
    public function test_applies_secure_defaults_in_production(): void
    {
        $this->app['env'] = 'production';
        config([
            'avicore.demo_login.enabled_flag' => true,
            'session.secure' => false,
            'session.same_site' => null,
        ]);

        ProductionSecurityConfig::apply();

        $this->assertFalse((bool) config('avicore.demo_login.enabled_flag'));
        $this->assertTrue((bool) config('session.secure'));
        $this->assertSame('lax', config('session.same_site'));
    }

    public function test_does_not_apply_outside_production(): void
    {
        $this->app['env'] = 'staging';
        config([
            'avicore.demo_login.enabled_flag' => true,
            'session.secure' => false,
        ]);

        ProductionSecurityConfig::apply();

        $this->assertTrue((bool) config('avicore.demo_login.enabled_flag'));
        $this->assertFalse((bool) config('session.secure'));
    }
}
