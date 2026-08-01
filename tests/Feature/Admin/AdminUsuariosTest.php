<?php

namespace Tests\Feature\Admin;

use App\Actions\User\CreateUserAction;
use App\Actions\User\ResetUserPasswordAction;
use App\Actions\User\UpdateUserAction;
use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Livewire\Admin\Usuarios\Index as UsuariosIndex;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsuariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_dueno_can_list_create_and_reset_password_for_company_users(): void
    {
        [$empresa, $dueno] = $this->empresaConDueno();

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Operario Uno',
            'documento' => '30111222',
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        User::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'name' => 'Ajeno',
            'documento' => '99999999',
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee('Operario Uno')
            ->assertDontSee('Ajeno')
            ->assertSee('Nuevo usuario');

        Livewire::actingAs($dueno)
            ->test(UsuariosIndex::class)
            ->call('abrirCrear')
            ->set('name', 'Nuevo Operario')
            ->set('documento', '40111222')
            ->set('email', 'nuevo@demo.test')
            ->set('rol', UserRole::Operario->value)
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('dialogPasswordAbierto', true)
            ->assertDispatched('snackbar-show');

        $creado = User::query()->where('documento', '40111222')->first();
        $this->assertNotNull($creado);
        $this->assertSame($empresa->id, $creado->empresa_id);
        $this->assertTrue($creado->must_change_password);
        $this->assertTrue($creado->activo);

        Livewire::actingAs($dueno)
            ->test(UsuariosIndex::class)
            ->call('resetearPassword', $creado->id)
            ->assertSet('dialogPasswordAbierto', true)
            ->assertSee('Contraseña temporal');

        $creado->refresh();
        $this->assertTrue($creado->must_change_password);
    }

    public function test_encargado_can_view_and_reset_but_cannot_create(): void
    {
        [$empresa, $dueno] = $this->empresaConDueno();

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Encargado Demo',
            'documento' => '50111222',
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Operario Reset',
            'documento' => '60111222',
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($encargado)
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertSee('Operario Reset')
            ->assertDontSee('wire:click="abrirCrear"', false);

        Livewire::actingAs($encargado)
            ->test(UsuariosIndex::class)
            ->call('abrirCrear')
            ->assertForbidden();

        Livewire::actingAs($encargado)
            ->test(UsuariosIndex::class)
            ->call('resetearPassword', $operario->id)
            ->assertSet('dialogPasswordAbierto', true);

        Livewire::actingAs($encargado)
            ->test(UsuariosIndex::class)
            ->call('abrirEditar', $operario->id)
            ->assertForbidden();

        Livewire::actingAs($encargado)
            ->test(UsuariosIndex::class)
            ->call('toggleActivo', $operario->id)
            ->assertForbidden();
    }

    public function test_admin_avicore_can_create_user_for_selected_company(): void
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Cliente Norte',
            'estado' => EmpresaEstado::Activa,
        ]);

        $admin = User::factory()->adminAvicore()->create([
            'documento' => '900000010',
            'must_change_password' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(UsuariosIndex::class)
            ->call('abrirCrear')
            ->set('name', 'Operario Cliente')
            ->set('documento', '90111222')
            ->set('rol', UserRole::Operario->value)
            ->set('empresa_id', (string) $empresa->id)
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('dialogPasswordAbierto', true);

        $creado = User::query()->where('documento', '90111222')->first();
        $this->assertNotNull($creado);
        $this->assertSame($empresa->id, $creado->empresa_id);
        $this->assertTrue($creado->must_change_password);
    }

    public function test_administrativo_cannot_assign_dueno_role(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $administrativo = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '70111222',
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        $this->expectException(ValidationException::class);

        app(CreateUserAction::class)->execute($administrativo, [
            'name' => 'Intentado Dueño',
            'documento' => '80111222',
            'email' => null,
            'rol' => UserRole::Dueno->value,
        ]);
    }

    public function test_cannot_access_users_from_other_company(): void
    {
        [$empresaA, $duenoA] = $this->empresaConDueno('11111111');

        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $operarioB = User::factory()->create([
            'empresa_id' => $empresaB->id,
            'documento' => '22222222',
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($duenoA)
            ->test(UsuariosIndex::class)
            ->call('abrirEditar', $operarioB->id);
    }

    public function test_operario_is_redirected_from_usuarios(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '33333333',
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('admin.usuarios.index'))
            ->assertRedirect(route('operario.home'));
    }

    public function test_update_and_toggle_active_actions(): void
    {
        [$empresa, $dueno] = $this->empresaConDueno('44444444');

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Editable',
            'documento' => '55555555',
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);

        app(UpdateUserAction::class)->execute($dueno, $operario, [
            'name' => 'Editable Nuevo',
            'documento' => '55555555',
            'email' => 'edit@demo.test',
            'rol' => UserRole::Encargado->value,
            'activo' => true,
        ]);

        $operario->refresh();
        $this->assertSame('Editable Nuevo', $operario->name);
        $this->assertSame(UserRole::Encargado, $operario->rol);

        Livewire::actingAs($dueno)
            ->test(UsuariosIndex::class)
            ->call('toggleActivo', $operario->id)
            ->assertDispatched('snackbar-show');

        $this->assertFalse($operario->fresh()->activo);
    }

    public function test_reset_password_hashes_new_temporary_password(): void
    {
        [$empresa, $dueno] = $this->empresaConDueno('66666666');
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '77777777',
            'rol' => UserRole::Operario,
            'password' => 'OldPassword1',
            'must_change_password' => false,
        ]);

        $result = app(ResetUserPasswordAction::class)->execute($dueno, $operario);

        $this->assertTrue(Hash::check($result['plainPassword'], $operario->fresh()->password));
        $this->assertTrue($operario->fresh()->must_change_password);
        $this->assertFalse(Hash::check('OldPassword1', $operario->fresh()->password));
    }

    public function test_admin_nav_links_to_usuarios_module(): void
    {
        [, $dueno] = $this->empresaConDueno('88888888');

        $this->actingAs($dueno)
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee(route('admin.usuarios.index'), false);
    }

    public function test_cannot_deactivate_own_account(): void
    {
        [$empresa, $dueno] = $this->empresaConDueno('90909090');

        $this->expectException(ValidationException::class);

        app(UpdateUserAction::class)->execute($dueno, $dueno, [
            'name' => $dueno->name,
            'documento' => $dueno->documento,
            'email' => $dueno->email,
            'rol' => $dueno->rol->value,
            'activo' => false,
        ]);
    }

    public function test_cannot_convert_company_user_to_admin_avicore(): void
    {
        [$empresa, $dueno] = $this->empresaConDueno('91919191');

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'documento' => '92929292',
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $admin = User::factory()->adminAvicore()->create([
            'documento' => '93939393',
            'must_change_password' => false,
        ]);

        $this->expectException(ValidationException::class);

        app(UpdateUserAction::class)->execute($admin, $operario, [
            'name' => $operario->name,
            'documento' => $operario->documento,
            'email' => $operario->email,
            'rol' => UserRole::AdminAvicore->value,
            'activo' => true,
        ]);
    }

    public function test_cannot_demote_admin_avicore_to_company_role(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'documento' => '94949494',
            'must_change_password' => false,
        ]);

        $this->expectException(ValidationException::class);

        app(UpdateUserAction::class)->execute($admin, $admin, [
            'name' => $admin->name,
            'documento' => $admin->documento,
            'email' => $admin->email,
            'rol' => UserRole::Dueno->value,
            'activo' => true,
        ]);
    }

    public function test_non_admin_cannot_update_admin_avicore_user(): void
    {
        [$empresa, $dueno] = $this->empresaConDueno('95959595');

        $admin = User::factory()->adminAvicore()->create([
            'documento' => '96969696',
            'must_change_password' => false,
        ]);

        $this->expectException(AuthorizationException::class);

        app(UpdateUserAction::class)->execute($dueno, $admin, [
            'name' => $admin->name,
            'documento' => $admin->documento,
            'email' => $admin->email,
            'rol' => $admin->rol->value,
            'activo' => true,
        ]);
    }

    /**
     * @return array{0: Empresa, 1: User}
     */
    private function empresaConDueno(string $documento = '20111222'): array
    {
        $empresa = Empresa::factory()->create([
            'nombre' => 'Avícola Demo',
            'estado' => EmpresaEstado::Activa,
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Dueño Demo',
            'documento' => $documento,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$empresa, $dueno];
    }
}
