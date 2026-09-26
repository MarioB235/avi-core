<?php

namespace Tests\Feature\Security;

use App\Services\DemoLoginService;
use App\Support\ProductionSecurityConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSecurityBootTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_security_config_disables_demo_login_at_runtime(): void
    {
        $this->app['env'] = 'production';
        config([
            'avicore.demo_login.enabled_flag' => true,
            'session.secure' => false,
            'session.same_site' => null,
        ]);

        ProductionSecurityConfig::apply();

        $this->assertFalse(app(DemoLoginService::class)->isEnabled());
        $this->assertTrue((bool) config('session.secure'));
        $this->assertSame('lax', config('session.same_site'));
    }
}
