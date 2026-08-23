<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePanelRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_has_dedicated_home_route(): void
    {
        $this->assertSame('dueno.home', UserRole::Dueno->homeRouteName());
        $this->assertSame('administrativo.home', UserRole::Administrativo->homeRouteName());
        $this->assertSame('encargado.home', UserRole::Encargado->homeRouteName());
        $this->assertSame('avicore.home', UserRole::AdminAvicore->homeRouteName());
        $this->assertSame('operario.home', UserRole::Operario->homeRouteName());
        $this->assertSame('reparto.home', UserRole::Reparto->homeRouteName());
    }

    public function test_legacy_admin_redirects_to_role_prefix(): void
    {
        $empresa = Empresa::factory()->create();

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get('/admin/resumen')
            ->assertRedirect('/dueno/resumen');
    }

    public function test_user_cannot_access_another_role_panel(): void
    {
        $empresa = Empresa::factory()->create();

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('encargado.resumen.index'))
            ->assertRedirect(route('dueno.home'));
    }

    public function test_reparto_user_reaches_reparto_stub_home(): void
    {
        $empresa = Empresa::factory()->create();

        $reparto = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Reparto,
            'must_change_password' => false,
        ]);

        $this->actingAs($reparto)
            ->get(route('reparto.home'))
            ->assertOk()
            ->assertSee('Módulo en desarrollo');
    }
}
