<?php

namespace Tests\Feature\Admin;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Admin\Resumen\Index as ResumenIndex;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResumenTest extends TestCase
{
    use RefreshDatabase;

    public function test_dueno_can_view_resumen_with_kpis(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 750,
            ]);

        $this->actingAs($dueno)
            ->get(route('dueno.resumen.index'))
            ->assertOk()
            ->assertSee('Resumen')
            ->assertSee('Indicadores del día')
            ->assertSee('750')
            ->assertSee('Por galpón')
            ->assertSee($galpon->nombre, false);

        Livewire::actingAs($dueno)
            ->test(ResumenIndex::class)
            ->assertSee('Huevos hoy')
            ->assertSee('750');
    }

    public function test_resumen_filters_by_granja(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
            ]);

        Livewire::actingAs($dueno)
            ->test(ResumenIndex::class)
            ->set('filtroGranjaId', (string) $galponA->granja_id)
            ->assertSee('400')
            ->assertSee('Galpón A')
            ->assertDontSee('Galpón B');
    }

    public function test_encargado_can_view_resumen(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->create();

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $this->actingAs($encargado)
            ->get(route('encargado.resumen.index'))
            ->assertOk()
            ->assertSee('Resumen');
    }

    public function test_operario_is_redirected_from_resumen(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('dueno.resumen.index'))
            ->assertRedirect(route('operario.home'));
    }

    public function test_resumen_toggle_galpon_filter_limits_kpis(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
            ]);

        Livewire::actingAs($dueno)
            ->test(ResumenIndex::class)
            ->call('toggleGalpon', $galponA->id)
            ->assertSet('filtroGalponIds', [$galponA->id])
            ->assertSee('400')
            ->assertSee('1 galpón');
    }

    public function test_resumen_ignores_toggle_for_galpon_from_other_company(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create();

        Livewire::actingAs($dueno)
            ->test(ResumenIndex::class)
            ->call('toggleGalpon', $galponAjeno->id)
            ->assertSet('filtroGalponIds', []);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function duenoConGalponYLote(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        Lote::factory()
            ->forGalpon($galpon)
            ->create([
                'cantidad_inicial' => 5000,
                'estado' => LoteEstado::EnProduccion,
            ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$dueno, $galpon];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon}
     */
    private function duenoConDosGalpones(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Granja A']);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Granja B']);
        $galponA = Galpon::factory()->forGranja($granjaA)->create(['nombre' => 'Galpón A']);
        $galponB = Galpon::factory()->forGranja($granjaB)->create(['nombre' => 'Galpón B']);

        foreach ([$galponA, $galponB] as $galpon) {
            Lote::factory()
                ->forGalpon($galpon)
                ->create([
                    'cantidad_inicial' => 3000,
                    'estado' => LoteEstado::EnProduccion,
                ]);
        }

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$dueno, $galponA, $galponB];
    }
}
