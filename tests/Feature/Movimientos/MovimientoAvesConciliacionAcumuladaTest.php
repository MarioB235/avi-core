<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarAjusteInventarioAvesAction;
use App\Actions\Movimiento\RegistrarCierreLoteAction;
use App\Actions\Movimiento\RegistrarEntradaAvesAction;
use App\Actions\Movimiento\RevertirMovimientoAvesAction;
use App\Actions\Operacion\RegistrarCargaDescarteAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Services\MovimientoAvesConciliacionService;
use App\Support\IdempotenciaCaptura;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovimientoAvesConciliacionAcumuladaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cuadra_lote_muertes_y_descarte(): void
    {
        [$encargado, $galpon, $operario] = $this->contextoConOperario();

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 1000],
            Carbon::today(),
        );

        app(RegistrarCargaMuertesAction::class)->execute($operario, $galpon, 25);
        app(RegistrarCargaDescarteAction::class)->execute($operario, $galpon, 10);

        $galpon->refresh();

        $resultado = app(MovimientoAvesConciliacionService::class)->conciliacionAcumulada($galpon);

        $this->assertSame(1000, $resultado['inicial']);
        $this->assertSame(0, $resultado['entradas']);
        $this->assertSame(0, $resultado['salidas']);
        $this->assertSame(25, $resultado['muertes']);
        $this->assertSame(10, $resultado['descartes']);
        $this->assertSame(965, $resultado['saldo_esperado']);
        $this->assertSame(965, $resultado['aves_actuales']);
        $this->assertTrue($resultado['cuadra']);
        $this->assertSame(0, $resultado['diferencia']);
    }

    public function test_refleja_entrada_ajuste_y_cierre_parcial(): void
    {
        [$encargado, $galpon, $operario, $lote] = $this->contextoConLoteRegistrado(800);

        app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            100,
            'Compra externa',
            IdempotenciaCaptura::generarClave(),
        );

        app(RegistrarCargaMuertesAction::class)->execute($operario, $galpon, 20);

        app(RegistrarAjusteInventarioAvesAction::class)->execute(
            $encargado,
            $galpon,
            875,
            'Conteo en galpón',
        );

        app(RegistrarCierreLoteAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            50,
            'Cierre parcial comercial',
            cerrarCicloLote: false,
        );

        $galpon->refresh();

        $resultado = app(MovimientoAvesConciliacionService::class)->conciliacionAcumulada($galpon);

        $this->assertSame(800, $resultado['inicial']);
        $this->assertSame(100, $resultado['entradas']);
        $this->assertSame(50, $resultado['salidas']);
        $this->assertSame(20, $resultado['muertes']);
        $this->assertSame(-5, $resultado['ajustes']);
        $this->assertSame(825, $resultado['saldo_esperado']);
        $this->assertTrue($resultado['cuadra']);
    }

    public function test_reversion_de_entrada_restaura_cuadre(): void
    {
        [$encargado, $galpon, , $lote] = $this->contextoConLoteRegistrado(500);

        $entrada = app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            80,
            'Entrada errónea',
            IdempotenciaCaptura::generarClave(),
        );

        $galpon->refresh();
        $antes = app(MovimientoAvesConciliacionService::class)->conciliacionAcumulada($galpon);
        $this->assertTrue($antes['cuadra']);
        $this->assertSame(80, $antes['entradas']);
        $this->assertSame(580, $antes['aves_actuales']);

        app(RevertirMovimientoAvesAction::class)->execute(
            $encargado,
            $entrada,
            'Anulación de entrada',
        );

        $galpon->refresh();

        $despues = app(MovimientoAvesConciliacionService::class)->conciliacionAcumulada($galpon);

        $this->assertSame(500, $despues['aves_actuales']);
        $this->assertTrue($despues['cuadra']);
        $this->assertSame(1, $despues['reversiones_registradas']);
    }

    public function test_expone_diferencia_cuando_saldo_vivo_no_coincide(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 200],
            Carbon::today(),
        );

        $galpon->refresh();
        $galpon->update(['aves_actuales' => 195]);
        $galpon->refresh();

        $resultado = app(MovimientoAvesConciliacionService::class)->conciliacionAcumulada($galpon);

        $this->assertFalse($resultado['cuadra']);
        $this->assertSame(-5, $resultado['diferencia']);
        $this->assertSame(200, $resultado['saldo_esperado']);
        $this->assertSame(195, $resultado['aves_actuales']);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function contextoEncargado(): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => 0,
            'activo' => true,
        ]);

        return [$encargado, $galpon];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: User}
     */
    private function contextoConOperario(): array
    {
        [$encargado, $galpon] = $this->contextoEncargado();
        $operario = User::factory()->create([
            'empresa_id' => $galpon->empresa_id,
            'rol' => UserRole::Operario,
        ]);

        return [$encargado, $galpon, $operario];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: User, 3: Lote}
     */
    private function contextoConLoteRegistrado(int $cantidad): array
    {
        [$encargado, $galpon, $operario] = $this->contextoConOperario();

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
