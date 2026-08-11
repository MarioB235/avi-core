<?php

namespace Tests\Feature\Operario;

use App\Actions\Auth\ChangePasswordAction;
use App\Actions\User\UpdateProfileAction;
use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Profile\Edit as ProfileEdit;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioPerfilTest extends TestCase
{
    use RefreshDatabase;

    public function test_operario_can_access_perfil_page(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'name' => 'Juan Operario',
        ]);

        $this->actingAs($operario)
            ->get(route('operario.perfil'))
            ->assertOk()
            ->assertSee('Mis datos')
            ->assertSee('Actualizá tu nombre y correo de contacto.')
            ->assertSee('Contraseña')
            ->assertSee($operario->documento)
            ->assertSee('avicore-operario-perfil', false)
            ->assertSee('avicore-operario-home-sheet', false);
    }

    public function test_operario_perfil_password_section_updates_hero_copy(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.perfil', ['seccion' => 'password']))
            ->assertOk()
            ->assertSee('Cambiá tu clave de acceso.')
            ->assertSee('Guardar contraseña')
            ->assertSee('avicore-operario-perfil-hero', false)
            ->assertSee('avicore-operario-perfil', false)
            ->assertSee('avicore-operario-home-sheet', false);
    }

    public function test_operario_perfil_tabs_switch_sections_without_wire_navigate(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('operario.perfil'))
            ->assertOk()
            ->assertSee('wire:click="seleccionarSeccion(\'password\')"', false)
            ->assertSee('wire:click="seleccionarSeccion(\'datos\')"', false)
            ->assertSee('avicore-operario-perfil__tabs', false);
    }

    public function test_operario_can_update_name_and_email(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'name' => 'Juan Operario',
            'email' => null,
        ]);

        $this->actingAs($operario);

        Livewire::test(ProfileEdit::class)
            ->set('name', 'Juan Actualizado')
            ->set('email', 'juan@demo.test')
            ->call('guardarDatos')
            ->assertHasNoErrors();

        $operario->refresh();

        $this->assertSame('Juan Actualizado', $operario->name);
        $this->assertSame('juan@demo.test', $operario->email);
    }

    public function test_operario_can_change_password_from_perfil(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'password' => 'ClaveActual1!',
            'must_change_password' => false,
        ]);

        $this->actingAs($operario);

        Livewire::withQueryParams(['seccion' => 'password'])
            ->test(ProfileEdit::class)
            ->set('current_password', 'ClaveActual1!')
            ->set('password', 'NuevaClave2026!')
            ->set('password_confirmation', 'NuevaClave2026!')
            ->call('guardarContrasena')
            ->assertHasNoErrors();

        $operario->refresh();

        $this->assertFalse($operario->must_change_password);
    }

    public function test_operario_can_switch_between_perfil_sections(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario);

        Livewire::test(ProfileEdit::class)
            ->assertSet('seccion', 'datos')
            ->assertSee('Guardar datos')
            ->call('seleccionarSeccion', 'password')
            ->assertSet('seccion', 'password')
            ->assertSee('Guardar contraseña')
            ->assertSee('Cambiá tu clave de acceso.')
            ->assertDontSee('Guardar datos')
            ->call('seleccionarSeccion', 'datos')
            ->assertSet('seccion', 'datos')
            ->assertSee('Guardar datos')
            ->assertSee('Actualizá tu nombre y correo de contacto.')
            ->assertDontSee('Guardar contraseña');
    }

    public function test_inactive_user_cannot_update_profile_via_action(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'activo' => false,
            'must_change_password' => false,
        ]);

        $this->expectException(AuthorizationException::class);

        app(UpdateProfileAction::class)->execute($operario, [
            'name' => 'Nombre bloqueado',
        ]);
    }

    public function test_inactive_user_cannot_change_password_via_action(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'password' => 'ClaveActual1!',
            'activo' => false,
            'must_change_password' => false,
        ]);

        $this->expectException(AuthorizationException::class);

        app(ChangePasswordAction::class)->execute($operario, 'ClaveActual1!', 'NuevaClave2026!');
    }

    public function test_admin_avicore_cannot_access_operario_perfil(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('operario.perfil'))
            ->assertRedirect(route('admin.home'));
    }

    public function test_dueno_can_access_profile_from_admin_route(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Mi perfil')
            ->assertSee('Guardar datos');
    }
}
