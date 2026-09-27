<?php

namespace Tests\Feature\Auth;

use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Enums\EmpresaEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Admin\Estructura\Index as EstructuraIndex;
use App\Livewire\Admin\Resumen\Index as ResumenIndex;
use App\Livewire\Admin\Usuarios\Index as UsuariosIndex;
use App\Livewire\Operario\CargarHub;
use App\Livewire\Operario\Historial;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmpresaIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resumen_filter_ignores_foreign_galpon(): void
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $dueno = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $empresaB->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create();

        Livewire::actingAs($dueno)
            ->test(ResumenIndex::class)
            ->set('filtroGalponId', (string) $galponAjeno->id)
            ->assertSet('filtroGalponId', '');
    }

    public function test_operario_cannot_register_eggs_on_foreign_galpon(): void
    {
        [$operario, $galponAjeno] = $this->operarioYGalponAjeno();

        $this->expectException(AuthorizationException::class);

        app(RegistrarCargaHuevosAction::class)->execute($operario, $galponAjeno, 100, 0);
    }

    public function test_operario_livewire_cannot_persist_eggs_on_foreign_galpon(): void
    {
        [$operario, $galponAjeno] = $this->operarioYGalponAjeno();
        $operario->forceFill(['ultimo_galpon_id' => $galponAjeno->id])->save();

        Livewire::actingAs($operario)
            ->test(CargarHub::class)
            ->call('abrirFormularioHuevos')
            ->set('huevos', '100')
            ->set('huevosDescarte', '0')
            ->call('guardarHuevos')
            ->assertSet('dialogHuevosAbierto', false)
            ->assertSet('selectorGalponAbierto', true);

        $this->assertSame(0, RegistroOperativo::query()->where('empresa_id', $operario->empresa_id)->count());
    }

    public function test_administrativo_cannot_open_foreign_user_for_edit(): void
    {
        [$empresaA, $adminA] = $this->empresaConAdministrativo();
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $operarioB = User::factory()->create([
            'empresa_id' => $empresaB->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        Livewire::actingAs($adminA)
            ->test(UsuariosIndex::class)
            ->call('abrirEditar', $operarioB->id)
            ->assertNotFound();
    }

    public function test_administrativo_cannot_open_foreign_granja_for_edit(): void
    {
        [, $adminA] = $this->empresaConAdministrativo();
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $empresaB->id]);

        Livewire::actingAs($adminA)
            ->test(EstructuraIndex::class)
            ->call('abrirEditarGranja', $granjaAjena->id)
            ->assertNotFound();
    }

    public function test_operario_cannot_anular_foreign_company_registro_via_livewire(): void
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $operario = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresaB->id]);
        $galponB = Galpon::factory()->forGranja($granjaB)->create();
        $operarioB = User::factory()->create([
            'empresa_id' => $empresaB->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $registroAjeno = RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $operarioB)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 500,
            ]);

        Livewire::actingAs($operario)
            ->test(Historial::class)
            ->set('detalleKey', 'registro-'.$registroAjeno->id)
            ->set('motivoAnulacion', 'Intento ajeno')
            ->call('anularRegistro');

        $registroAjeno->refresh();
        $this->assertSame(RegistroOperativoEstado::Activo, $registroAjeno->estado);
    }

    public function test_administrativo_cannot_create_galpon_in_foreign_granja(): void
    {
        [, $adminA] = $this->empresaConAdministrativo();
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $empresaB->id]);

        Livewire::actingAs($adminA)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'galpones')
            ->call('abrirCrearGalpon')
            ->set('galponGranjaId', (string) $granjaAjena->id)
            ->set('galponNombre', 'Galpón ilegal')
            ->call('guardarGalpon')
            ->assertHasErrors(['galponGranjaId']);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function operarioYGalponAjeno(): array
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $operario = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $empresaB->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create();

        return [$operario, $galponAjeno];
    }

    /**
     * @return array{0: Empresa, 1: User}
     */
    private function empresaConAdministrativo(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $admin = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Administrativo,
            'must_change_password' => false,
        ]);

        return [$empresa, $admin];
    }
}
