<?php

namespace Tests\Feature\Ui;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminUserMenuTest extends TestCase
{
    use RefreshDatabase;

    private string $buildMetaPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buildMetaPath = public_path('build/avicore-build.json');
    }

    protected function tearDown(): void
    {
        if (File::exists($this->buildMetaPath)) {
            File::delete($this->buildMetaPath);
        }

        parent::tearDown();
    }

    public function test_admin_home_renders_shared_user_menu_in_sidebar_and_home_nav(): void
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
            'documento' => '20111222',
        ]);

        File::ensureDirectoryExists(public_path('build'));
        File::put($this->buildMetaPath, json_encode([
            'built_at' => '2026-08-01T14:30:00+00:00',
            'commit' => 'abc1234',
        ], JSON_THROW_ON_ERROR));

        $this->actingAs($dueno)
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('avicore-user-menu--sidebar', false)
            ->assertSee('avicore-operario-sidebar__menu-trigger', false)
            ->assertSee('avicore-home-nav__avatar', false)
            ->assertSee('avicore-user-menu__panel--portal', false)
            ->assertSee('syncPanelPosition', false)
            ->assertSee('x-teleport="body"', false)
            ->assertSee('Abrir menú de cuenta', false)
            ->assertSee('Perfil', false)
            ->assertSee('Cerrar sesión', false)
            ->assertSee('Documento', false)
            ->assertSee('20111222', false)
            ->assertSee('Versión', false)
            ->assertSee('abc1234', false)
            ->assertDontSee('avicore-admin-header__user-avatar', false)
            ->assertSee(route('admin.usuarios.index'), false)
            ->assertSee('Disponible', false)
            ->assertSee('Resumen de Avícola Demo', false);
    }

    public function test_usuarios_page_renders_shared_user_menu_in_sidebar_and_home_nav(): void
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
            'documento' => '20111222',
        ]);

        $this->actingAs($dueno)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee('avicore-user-menu--sidebar', false)
            ->assertSee('avicore-operario-sidebar__menu-trigger', false)
            ->assertSee('avicore-home-nav__avatar', false)
            ->assertSee('avicore-user-menu__panel--portal', false)
            ->assertSee('syncPanelPosition', false)
            ->assertSee('x-teleport="body"', false)
            ->assertSee('Abrir menú de cuenta', false)
            ->assertSee('Gestioná el equipo', false)
            ->assertDontSee('avicore-admin-header__user-avatar', false);
    }
}
