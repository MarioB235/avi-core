<?php

namespace Tests\Unit\Services\Auth;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Services\Auth\AccountAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountAccessServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_with_active_empresa_may_use_application(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'activo' => true,
        ]);

        $this->assertTrue(app(AccountAccessService::class)->mayUseApplication($user));
    }

    public function test_inactive_user_is_denied(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'activo' => false,
        ]);

        $this->assertFalse(app(AccountAccessService::class)->mayUseApplication($user));
    }

    public function test_admin_avicore_without_empresa_may_use_application_when_active(): void
    {
        $user = User::factory()->adminAvicore()->create(['activo' => true]);

        $this->assertTrue(app(AccountAccessService::class)->mayUseApplication($user));
    }

    public function test_suspended_empresa_denies_non_admin_users(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Suspendida]);
        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'activo' => true,
        ]);

        $this->assertFalse(app(AccountAccessService::class)->mayUseApplication($user));
    }
}
