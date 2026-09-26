<?php

namespace App\Support;

class ProductionSecurityConfig
{
    public static function apply(): void
    {
        if (! app()->environment('production')) {
            return;
        }

        config([
            'avicore.demo_login.enabled_flag' => false,
            'session.secure' => true,
            'session.same_site' => 'lax',
        ]);
    }
}
