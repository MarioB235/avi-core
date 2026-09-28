<?php

namespace Tests\Feature\Admin;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Enums\VacunaTipo;
use App\Livewire\Admin\HistorialOperativo\Index as HistorialOperativoIndex;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use App\Support\DiaOperativoEmpresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AdminHistorialOperativoTest extends TestCase
{
    use RefreshDatabase;

    public function test_encargado_ve_registros_de_operarios_ajenos(): void
    {
        [$encargado, $operario, $galpon] = $this->supervisorConOperario();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 880,
            ]);

        $this->assertTrue(Gate::forUser($encargado)->allows('admin.viewHistorialOperativo'));

        Livewire::actingAs($encargado)
            ->test(HistorialOperativoIndex::class)
            ->assertSee('880 huevos aptos', false)
            ->assertSee($operario->name, false)
            ->call('abrirDetalle', 'registro-'.$registro->id)
            ->assertSet('dialogDetalleAbierto', true)
            ->assertSee('Registrado por', false)
            ->assertSee('Corregir registro', false);
    }

    public function test_dueno_filtra_por_galpon_operario_tipo_y_estado(): void
    {
        [$dueno, $operarioA, $operarioB, $galponA, $galponB] = $this->duenoConDosGalponesYOperarios();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $operarioA)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 500,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $operarioB)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 2,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $operarioA)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 100,
                'estado' => RegistroOperativoEstado::Anulado,
                'motivo_anulacion' => 'Error',
                'anulado_at' => now(),
                'anulado_por' => $operarioA->id,
            ]);

        $this->actingAs($dueno)
            ->get(route('dueno.historial-operativo.index'))
            ->assertOk()
            ->assertSee('Historial operativo', false);

        Livewire::actingAs($dueno)
            ->test(HistorialOperativoIndex::class)
            ->set('filtroGalponId', (string) $galponA->id)
            ->assertSee('500 huevos aptos', false)
            ->assertSee('100 huevos aptos', false)
            ->assertDontSee('2 muertes', false)
            ->set('filtroOperarioId', (string) $operarioB->id)
            ->assertDontSee('500 huevos aptos', false)
            ->set('filtroOperarioId', '')
            ->set('filtroTipo', RegistroOperativoTipo::Huevos->value)
            ->assertSee('500 huevos aptos', false)
            ->assertSee('100 huevos aptos', false)
            ->assertDontSee('2 muertes', false)
            ->set('filtroEstado', RegistroOperativoEstado::Anulado->value)
            ->assertSee('100 huevos aptos', false)
            ->assertDontSee('500 huevos aptos', false);
    }

    public function test_historial_supervisor_incluye_vacunaciones_y_filtro_periodo(): void
    {
        [$encargado, $operario, $galpon] = $this->supervisorConOperario();
        $lote = Lote::factory()->forGalpon($galpon)->create([
            'codigo' => 'L-SUP',
            'estado' => LoteEstado::EnProduccion,
        ]);

        $ayer = DiaOperativoEmpresa::ayerParaEmpresa((int) $encargado->empresa_id)->fechaLogica->toDateString();
        $hoy = DiaOperativoEmpresa::fechaLogicaHoy((int) $encargado->empresa_id);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Alimento,
                'huevos' => null,
                'alimento_kg' => 10,
                'created_at' => DiaOperativoEmpresa::enFechaParaEmpresa((int) $encargado->empresa_id, $ayer)
                    ->fechaLogica->copy()->setTime(9, 0)->utc(),
            ]);

        Vacunacion::factory()
            ->forLote($lote, $operario)
            ->create([
                'vacuna' => VacunaTipo::Newcastle,
                'created_at' => DiaOperativoEmpresa::hoyParaEmpresa((int) $encargado->empresa_id)
                    ->fechaLogica->copy()->setTime(11, 0)->utc(),
            ]);

        Livewire::actingAs($encargado)
            ->test(HistorialOperativoIndex::class)
            ->assertSee('Newcastle', false)
            ->assertSee('10,00 kg entregados', false)
            ->set('filtroTipo', 'vacunacion')
            ->assertSee('Newcastle', false)
            ->assertDontSee('10,00 kg entregados', false)
            ->set('filtroTipo', '')
            ->set('fechaDesde', $hoy)
            ->set('fechaHasta', $hoy)
            ->assertSee('Newcastle', false)
            ->assertDontSee('10,00 kg entregados', false);
    }

    public function test_historial_supervisor_no_muestra_registros_de_otra_empresa(): void
    {
        [$encargado, $operario, $galpon] = $this->supervisorConOperario();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 321,
            ]);

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $otraGranja = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($otraGranja)->create();
        $operarioAjeno = User::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponAjeno, $operarioAjeno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 9999,
            ]);

        Livewire::actingAs($encargado)
            ->test(HistorialOperativoIndex::class)
            ->assertSee('321 huevos aptos', false)
            ->assertDontSee('9.999 huevos aptos', false);
    }

    public function test_operario_no_puede_acceder_al_historial_supervisor(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
            ]);

        $this->actingAs($operario)
            ->get(route('dueno.historial-operativo.index'))
            ->assertRedirect(route('operario.home'));

        Livewire::actingAs($operario)
            ->test(HistorialOperativoIndex::class)
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: User, 2: Galpon}
     */
    private function supervisorConOperario(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'name' => 'Pedro Operario',
        ]);

        return [$encargado, $operario, $galpon];
    }

    /**
     * @return array{0: User, 1: User, 2: User, 3: Galpon, 4: Galpon}
     */
    private function duenoConDosGalponesYOperarios(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);

        $galponA = Galpon::factory()->forGranja($granja)->create(['nombre' => 'Galpón A']);
        $galponB = Galpon::factory()->forGranja($granja)->create(['nombre' => 'Galpón B']);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $operarioA = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'name' => 'Operario A',
        ]);

        $operarioB = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'name' => 'Operario B',
        ]);

        return [$dueno, $operarioA, $operarioB, $galponA, $galponB];
    }
}
