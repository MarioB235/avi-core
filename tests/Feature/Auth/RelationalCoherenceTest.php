<?php

namespace Tests\Feature\Auth;

use App\Actions\Galpon\UpdateGalponAction;
use App\Actions\Operacion\RegistrarVacunacionAction;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\UserRole;
use App\Enums\VacunaTipo;
use App\Livewire\Admin\Estructura\Index as EstructuraIndex;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class RelationalCoherenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_galpon_action_rejects_foreign_granja(): void
    {
        [$empresa, $admin] = $this->empresaConAdministrativo();
        $granjaPropia = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granjaPropia)->create();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);

        $this->expectException(ValidationException::class);

        app(UpdateGalponAction::class)->execute($admin, $galpon, [
            'granja_id' => $granjaAjena->id,
            'nombre' => $galpon->nombre,
            'estado' => $galpon->estado->value,
            'activo' => $galpon->activo,
        ]);
    }

    public function test_vacunacion_rejects_lote_from_other_galpon_same_empresa(): void
    {
        [$operario, $galponA, $loteAjeno] = $this->operarioConDosGalpones();

        $this->expectException(ValidationException::class);

        app(RegistrarVacunacionAction::class)->execute(
            $operario,
            $galponA,
            $loteAjeno,
            VacunaTipo::Pox,
        );
    }

    public function test_admin_estructura_rejects_lote_on_foreign_galpon(): void
    {
        [, $admin] = $this->empresaConAdministrativo();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create();

        Livewire::actingAs($admin)
            ->test(EstructuraIndex::class)
            ->set('seccion', 'lotes')
            ->call('abrirCrearLote')
            ->set('loteGalponId', (string) $galponAjeno->id)
            ->set('loteTipoHuevo', 'blanco')
            ->set('loteCantidad', '100')
            ->set('loteFechaNacimiento', now()->subWeeks(18)->format('Y-m-d'))
            ->call('guardarLoteCrear')
            ->assertNotFound();

        $this->assertDatabaseMissing('lotes', ['galpon_id' => $galponAjeno->id]);
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

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function operarioConDosGalpones(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galponA = Galpon::factory()->forGranja($granja)->create();
        $galponB = Galpon::factory()->forGranja($granja)->create();
        $loteAjeno = Lote::factory()->forGalpon($galponB)->create([
            'estado' => LoteEstado::EnProduccion,
        ]);

        return [$operario, $galponA, $loteAjeno];
    }
}
