<?php

namespace Tests\Feature\Reportes;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarEntradaAvesAction;
use App\Actions\Movimiento\RevertirMovimientoAvesAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Services\MovimientoAvesConciliacionService;
use App\Services\ReporteConsultaService;
use App\Services\ReporteMovimientosExistenciasExcelExporter;
use App\Support\IdempotenciaCaptura;
use App\Support\ReporteFiltroProduccion;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteMovimientosExistenciasTest extends TestCase
{
    use RefreshDatabase;

    public function test_consulta_cuadra_con_movimiento_aves_conciliacion_rep05(): void
    {
        [$encargado, $galpon, $operario, $lote] = $this->contextoConLote(500);

        app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            80,
            'Compra',
            IdempotenciaCaptura::generarClave(),
        );

        app(RegistrarCargaMuertesAction::class)->execute($operario, $galpon, 15);

        $galpon->refresh();

        $filtro = ReporteFiltroProduccion::diaUnico($encargado, null, $galpon->id, now());
        $consulta = app(ReporteConsultaService::class)->movimientosExistencias($filtro);
        $directo = app(MovimientoAvesConciliacionService::class)->conciliacionAcumulada(
            $galpon,
            $filtro->fechaDesde,
            $filtro->fechaHasta,
        );

        $this->assertFalse($consulta['vacio']);
        $this->assertCount(1, $consulta['bloques_galpon']);
        $this->assertSame($directo, $consulta['bloques_galpon'][0]['conciliacion']);
        $this->assertSame(565, $directo['saldo_esperado']);
        $this->assertTrue($directo['cuadra']);
    }

    public function test_ledger_marca_reversion_rep05(): void
    {
        [$encargado, $galpon, , $lote] = $this->contextoConLote(400);

        $entrada = app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            50,
            'Error',
            IdempotenciaCaptura::generarClave(),
        );

        app(RevertirMovimientoAvesAction::class)->execute($encargado, $entrada, 'Anular entrada');

        $filtro = ReporteFiltroProduccion::diaUnico($encargado, null, $galpon->id, now());
        $consulta = app(ReporteConsultaService::class)->movimientosExistencias($filtro);

        $reversiones = array_filter(
            $consulta['bloques_galpon'][0]['movimientos'],
            fn (array $fila): bool => $fila['es_reversion'],
        );

        $this->assertCount(1, $reversiones);
        $this->assertSame(1, $consulta['bloques_galpon'][0]['conciliacion']['reversiones_registradas']);
    }

    public function test_excel_genera_y_ruta_responde_rep05(): void
    {
        [$encargado, $galpon] = $this->contextoConLote(300);

        $filtro = ReporteFiltroProduccion::diaUnico($encargado, null, $galpon->id, now());
        $bytes = app(ReporteMovimientosExistenciasExcelExporter::class)->generar($filtro);

        $this->assertGreaterThan(2000, strlen($bytes));
        $this->assertStringStartsWith('PK', $bytes);

        $this->actingAs($encargado)
            ->get(route('encargado.reportes.movimientos-existencias', ['galpon' => $galpon->id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_operario_redirige_desde_ruta_movimientos_rep05(): void
    {
        $empresa = Empresa::factory()->create();
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('encargado.reportes.movimientos-existencias'))
            ->assertRedirect();
    }

    /**
     * @return array{0: User, 1: Galpon, 2: User, 3: Lote}
     */
    private function contextoConLote(int $cantidad): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => 0,
            'activo' => true,
        ]);

        $lotes = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => $cantidad],
            Carbon::today(),
        );

        $galpon->refresh();

        return [$encargado, $galpon, $operario, $lotes->first()];
    }
}
