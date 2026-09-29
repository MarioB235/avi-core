<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarEntradaAvesAction;
use App\Actions\Movimiento\RegistrarTrasladoAvesAction;
use App\Actions\Movimiento\RevertirMovimientoAvesAction;
use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesTipo;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Support\IdempotenciaCaptura;
use App\Support\IdempotenciaMovimiento;
use App\Support\MovimientoAvesEfecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoAvesReversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_revierte_entrada_externa_y_restaura_saldo(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoConLote(500);

        $entrada = app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            120,
            'Ingreso erróneo',
            IdempotenciaCaptura::generarClave(),
        );

        $galpon->refresh();
        $this->assertSame(620, $galpon->aves_actuales);

        $reversion = app(RevertirMovimientoAvesAction::class)->execute(
            $encargado,
            $entrada,
            'Anulación por error de carga',
        );

        $galpon->refresh();
        $entrada->refresh();

        $this->assertSame(MovimientoAvesTipo::Reversion, $reversion->tipo);
        $this->assertSame(MovimientoAvesEstado::Reversado, $entrada->estado);
        $this->assertSame($reversion->id, $entrada->reversado_por_id);
        $this->assertSame(500, $galpon->aves_actuales);
    }

    public function test_revierte_traslado_y_restaura_ambos_galpones(): void
    {
        [$encargado, $origen, $destino, $lote] = $this->contextoDosGalpones();

        $traslado = app(RegistrarTrasladoAvesAction::class)->execute(
            $encargado,
            $origen,
            $destino,
            $lote,
            300,
            'Traslado erróneo',
        );

        app(RevertirMovimientoAvesAction::class)->execute(
            $encargado,
            $traslado,
            'Corrección de traslado',
        );

        $origen->refresh();
        $destino->refresh();

        $this->assertSame(1000, $origen->aves_actuales);
        $this->assertSame(0, $destino->aves_actuales);
    }

    public function test_segunda_reversion_devuelve_la_misma_sin_duplicar(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoConLote(400);

        $entrada = app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            50,
            'Entrada',
            IdempotenciaCaptura::generarClave(),
        );

        $action = app(RevertirMovimientoAvesAction::class);
        $primera = $action->execute($encargado, $entrada, 'Primera reversión válida');
        $segunda = $action->execute($encargado, $entrada->fresh(), 'Segunda reversión');

        $galpon->refresh();

        $this->assertSame($primera->id, $segunda->id);
        $this->assertSame(400, $galpon->aves_actuales);
        $this->assertSame(1, MovimientoAves::query()->where('tipo', MovimientoAvesTipo::Reversion)->count());
    }

    public function test_rechaza_reversion_con_movimiento_posterior(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoConLote(600);

        $primera = app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            100,
            'Primera entrada',
            IdempotenciaCaptura::generarClave(),
            Carbon::yesterday(),
        );

        app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            80,
            'Segunda entrada',
            IdempotenciaCaptura::generarClave(),
            Carbon::today(),
        );

        $this->expectException(ValidationException::class);

        try {
            app(RevertirMovimientoAvesAction::class)->execute(
                $encargado,
                $primera,
                'Intento fuera de orden',
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('movimientoId', $exception->errors());

            throw $exception;
        }
    }

    public function test_rechaza_reversion_que_dejaria_saldo_negativo(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoConLote(20);

        $entrada = app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            50,
            'Entrada grande',
            IdempotenciaCaptura::generarClave(),
        );

        $galpon->update(['aves_actuales' => 5]);

        $this->expectException(ValidationException::class);

        try {
            app(RevertirMovimientoAvesAction::class)->execute(
                $encargado,
                $entrada,
                'Revertir con saldo insuficiente',
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('movimientoId', $exception->errors());

            throw $exception;
        }
    }

    public function test_reversion_idempotente(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoConLote(200);

        $entrada = app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            40,
            'Entrada',
            IdempotenciaCaptura::generarClave(),
        );

        $action = app(RevertirMovimientoAvesAction::class);

        $primera = $action->execute($encargado, $entrada, 'Revertir una vez');
        $segunda = $action->execute($encargado, $entrada->fresh(), 'Revertir otra vez');

        $this->assertSame($primera->id, $segunda->id);
        $this->assertSame(1, MovimientoAves::query()
            ->where('idempotencia_clave', IdempotenciaMovimiento::claveReversionMovimiento($entrada->id))
            ->count());
    }

    public function test_ledger_sin_movimiento_original_reversado(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoConLote(300);

        $entrada = app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            60,
            'Entrada',
            IdempotenciaCaptura::generarClave(),
        );

        $reversion = app(RevertirMovimientoAvesAction::class)->execute(
            $encargado,
            $entrada,
            'Revertir entrada',
        );

        $saldo = MovimientoAvesEfecto::saldoNetoEnGalpon(
            [$entrada->fresh(), $reversion],
            $galpon->id,
        );

        $this->assertSame(0, $saldo);
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function contextoConLote(int $cantidad): array
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

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => $cantidad],
            Carbon::today(),
        )->first();

        $galpon->refresh();

        return [$encargado, $galpon, $lote];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon, 3: Lote}
     */
    private function contextoDosGalpones(): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $origen = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => 0,
            'activo' => true,
        ]);
        $destino = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => 0,
            'activo' => true,
        ]);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $origen,
            [TipoHuevo::Blanco->value => 1000],
            Carbon::today(),
        )->first();

        return [$encargado, $origen, $destino, $lote];
    }
}
