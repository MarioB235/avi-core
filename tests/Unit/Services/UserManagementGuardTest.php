<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Services\UserManagementGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UserManagementGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_deactivating_administrativo_when_another_active_manager_exists(): void
    {
        $empresa = Empresa::factory()->create();
        $adminA = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'activo' => true,
        ]);
        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'activo' => true,
        ]);

        app(UserManagementGuard::class)->assertCompanyRetainsActiveManager(
            $adminA,
            UserRole::Administrativo,
            false,
        );

        $this->assertTrue(true);
    }

    public function test_rejects_deactivating_last_active_administrativo(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'activo' => true,
        ]);

        $this->expectException(ValidationException::class);

        app(UserManagementGuard::class)->assertCompanyRetainsActiveManager(
            $admin,
            UserRole::Administrativo,
            false,
        );
    }

    public function test_rejects_demoting_last_active_administrativo(): void
    {
        $empresa = Empresa::factory()->create();
        $admin = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'activo' => true,
        ]);

        $this->expectException(ValidationException::class);

        app(UserManagementGuard::class)->assertCompanyRetainsActiveManager(
            $admin,
            UserRole::Encargado,
            true,
        );
    }
}
