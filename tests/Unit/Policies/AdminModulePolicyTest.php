<?php

namespace Tests\Unit\Policies;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Policies\AdminModulePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModulePolicyTest extends TestCase
{
    use RefreshDatabase;

    private AdminModulePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = app(AdminModulePolicy::class);
    }

    public function test_dueno_can_view_resumen_equipo_and_comercial(): void
    {
        $user = $this->userWithRole(UserRole::Dueno);

        $this->assertTrue($this->policy->viewResumen($user));
        $this->assertTrue($this->policy->viewHistorialOperativo($user));
        $this->assertTrue($this->policy->viewAuditoria($user));
        $this->assertTrue($this->policy->viewEquipo($user));
        $this->assertTrue($this->policy->viewComercial($user));
    }

    public function test_administrativo_can_view_resumen_but_not_equipo_or_comercial(): void
    {
        $user = $this->userWithRole(UserRole::Administrativo);

        $this->assertTrue($this->policy->viewResumen($user));
        $this->assertTrue($this->policy->viewHistorialOperativo($user));
        $this->assertTrue($this->policy->viewAuditoria($user));
        $this->assertFalse($this->policy->viewEquipo($user));
        $this->assertFalse($this->policy->viewComercial($user));
    }

    public function test_operario_cannot_view_admin_modules(): void
    {
        $user = $this->userWithRole(UserRole::Operario);

        $this->assertFalse($this->policy->viewResumen($user));
        $this->assertFalse($this->policy->viewHistorialOperativo($user));
        $this->assertFalse($this->policy->viewAuditoria($user));
        $this->assertFalse($this->policy->viewEquipo($user));
        $this->assertFalse($this->policy->viewComercial($user));
    }

    private function userWithRole(UserRole $rol): User
    {
        $empresa = Empresa::factory()->create();

        return User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => $rol,
        ]);
    }
}
