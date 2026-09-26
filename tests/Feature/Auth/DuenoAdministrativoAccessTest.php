<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\RoleCapabilitiesMatrix;
use Tests\TestCase;

class DuenoAdministrativoAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: UserRole, 2: bool}>
     */
    public static function panelModuleProvider(): array
    {
        $cases = [];

        foreach (RoleCapabilitiesMatrix::panelModuleAccess() as $module => $access) {
            $cases["dueno::{$module}"] = [$module, UserRole::Dueno, $access['dueno']];
            $cases["administrativo::{$module}"] = [$module, UserRole::Administrativo, $access['administrativo']];
        }

        return $cases;
    }

    #[DataProvider('panelModuleProvider')]
    public function test_panel_module_access_matches_d02_matrix(
        string $module,
        UserRole $role,
        bool $allowed,
    ): void {
        $empresa = Empresa::factory()->create();
        $user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => $role,
            'must_change_password' => false,
        ]);

        $response = $this->actingAs($user)->get(route("{$role->routePrefix()}.{$module}.index"));

        if ($allowed) {
            $response->assertOk();
        } else {
            $response->assertForbidden();
        }
    }

    public function test_both_roles_can_use_operario_mobile(): void
    {
        $empresa = Empresa::factory()->create();

        foreach ([UserRole::Dueno, UserRole::Administrativo] as $role) {
            $user = User::factory()->create([
                'empresa_id' => $empresa->id,
                'rol' => $role,
                'must_change_password' => false,
            ]);

            $this->actingAs($user)
                ->get(route('operario.home'))
                ->assertOk();
        }
    }
}
