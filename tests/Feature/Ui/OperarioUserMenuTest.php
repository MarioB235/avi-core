<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperarioUserMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_header_renders_user_menu_with_profile_and_logout(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
            'nombre' => 'Avícola Demo',
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'name' => 'Juan Operario',
            'documento' => '12345678',
        ]);

        $this->actingAs($operario)
            ->get(route('operario.home'))
            ->assertOk()
            ->assertSee('avicore-user-menu--sidebar', false)
            ->assertSee('avicore-user-menu__panel--portal', false)
            ->assertSee('syncPanelPosition', false)
            ->assertSee('panelHeight', false)
            ->assertSee('max-height:calc(100vh', false)
            ->assertSee('x-teleport="body"', false)
            ->assertSee('avicore-user-menu__panel', false)
            ->assertSee('avicore-home-nav__account-role', false)
            ->assertSee('Operario', false)
            ->assertSee('role="menu"', false)
            ->assertSee('aria-haspopup="menu"', false)
            ->assertSee('x-bind:aria-expanded', false)
            ->assertSee('aria-controls="avicore-user-menu-', false)
            ->assertSee('Perfil', false)
            ->assertSee('Cerrar sesión', false)
            ->assertSee('Documento', false)
            ->assertSee('12345678', false)
            ->assertSee('Empresa', false)
            ->assertSee('Avícola Demo', false)
            ->assertSee(route('logout'), false);
    }

    public function test_operario_cargar_header_renders_user_menu_trigger(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.cargar'))
            ->assertOk()
            ->assertSee('avicore-user-menu__trigger', false)
            ->assertSee('Abrir menú de cuenta', false)
            ->assertSee('aria-haspopup="menu"', false)
            ->assertSee('aria-controls="avicore-user-menu-', false);
    }

    public function test_operario_historial_header_renders_user_menu_trigger(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.historial'))
            ->assertOk()
            ->assertSee('avicore-user-menu__trigger', false)
            ->assertSee('Abrir menú de cuenta', false)
            ->assertSee('aria-haspopup="menu"', false);
    }
}
