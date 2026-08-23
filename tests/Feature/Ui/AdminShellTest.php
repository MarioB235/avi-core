<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use App\Support\AdminNav;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_dueno_home_uses_gestion_shell_without_campo(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
            'nombre' => 'Avícola Demo',
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
            'name' => 'María Dueña',
        ]);

        $this->assertSame(['Inicio', 'Resumen', 'Equipo', 'Comercial'], collect(AdminNav::tabs($dueno))->pluck('label')->all());

        $this->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertOk()
            ->assertSee('avicore-operario-shell', false)
            ->assertSee('avicore-operario-tab-bar', false)
            ->assertSee('avicore-operario-sidebar', false)
            ->assertSee('avicore-home-nav', false)
            ->assertSee('avicore-operario-home-sheet', false)
            ->assertSee('Inicio', false)
            ->assertSee('Resumen', false)
            ->assertSee('Equipo', false)
            ->assertSee('Comercial', false)
            ->assertDontSee('>Usuarios<', false)
            ->assertDontSee('>Estructura<', false)
            ->assertDontSee('¿Qué querés gestionar?')
            ->assertDontSee(route('operario.home'), false)
            ->assertDontSee('Cargar en galpón', false)
            ->assertDontSee('>Campo<', false)
            ->assertDontSee('avicore-admin-header--toolbar', false);
    }

    public function test_admin_avicore_nav_matches_gestion_tabs(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $this->assertSame(['Inicio', 'Usuarios'], collect(AdminNav::tabs($admin))->pluck('label')->all());

        $this->actingAs($admin)
            ->get(route('avicore.home'))
            ->assertOk()
            ->assertSee('Administración AviCore', false)
            ->assertDontSee('>Campo<', false)
            ->assertDontSee(route('operario.home'), false);
    }

    public function test_admin_bottom_nav_shares_tab_bar_component_with_operario(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.home'))
            ->assertOk()
            ->assertSee('--avicore-tab-cols: 4', false)
            ->assertSee('aria-label="Navegación panel"', false)
            ->assertSee('avicore-operario-tab-bar__inner', false)
            ->assertDontSee('avicore-operario-tab-bar__inner--cols-4', false);
    }

    public function test_dueno_cannot_access_usuarios_module(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.usuarios.index'))
            ->assertForbidden();
    }

    public function test_usuarios_page_uses_same_shell_heroes(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        $this->actingAs($administrativo)
            ->get(route('administrativo.usuarios.index'))
            ->assertOk()
            ->assertSee('avicore-operario-shell--home', false)
            ->assertSee('avicore-operario-home-hero', false)
            ->assertSee('Gestioná el equipo', false)
            ->assertSee('avicore-operario-tab-bar__item--active', false);
    }
}
