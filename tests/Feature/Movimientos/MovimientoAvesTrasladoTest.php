<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarTrasladoAvesAction;
use App\Enums\GalponEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use App\Support\IdempotenciaCaptura;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoAvesTrasladoTest extends TestCase
{
    use RefreshDatabase;

    public function test_traslado_atomico_conserva_total_y_actualiza_saldos(): void
    {
        [$encargado, $origen, $destino] = $this->contextoDosGalpones(0, 200);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $origen,
            [TipoHuevo::Blanco->value => 3000],
            Carbon::parse('2026-03-01'),
        )->first();

        $origen->refresh();
        $destino->refresh();
        $totalAntes = $origen->aves_actuales + $destino->aves_actuales;

        $movimiento = app(RegistrarTrasladoAvesAction::class)->execute(
            $encargado,
            $origen,
            $destino,
            $lote,
            800,
            'Traslado por capacidad',
        );

        $origen->refresh();
        $destino->refresh();

        $this->assertSame(MovimientoAvesTipo::Traslado, $movimiento->tipo);
        $this->assertSame(MovimientoAvesOrigen::TrasladoOperativo->value, $movimiento->metadata['origen']);
        $this->assertSame(2200, $origen->aves_actuales);
        $this->assertSame(1000, $destino->aves_actuales);
        $this->assertSame($totalAntes, $origen->aves_actuales + $destino->aves_actuales);
    }

    public function test_traslado_idempotente_no_duplica_efecto(): void
    {
        [$encargado, $origen, $destino] = $this->contextoDosGalpones(0, 0);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $origen,
            [TipoHuevo::Blanco->value => 1500],
            Carbon::today(),
        )->first();

        $clave = IdempotenciaCaptura::generarClave();
        $action = app(RegistrarTrasladoAvesAction::class);

        $primero = $action->execute($encargado, $origen, $destino, $lote, 400, 'Traslado', null, null, $clave);
        $segundo = $action->execute($encargado, $origen, $destino, $lote, 400, 'Traslado', null, null, $clave);

        $origen->refresh();
        $destino->refresh();

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1100, $origen->aves_actuales);
        $this->assertSame(400, $destino->aves_actuales);
    }

    public function test_traslado_rechazado_por_saldo_insuficiente_no_muta_galpones(): void
    {
        [$encargado, $origen, $destino] = $this->contextoDosGalpones(0, 100);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $origen,
            [TipoHuevo::Blanco->value => 500],
            Carbon::today(),
        )->first();

        $this->expectException(ValidationException::class);

        try {
            app(RegistrarTrasladoAvesAction::class)->execute(
                $encargado,
                $origen,
                $destino,
                $lote,
                600,
                'Intento exceso',
            );
        } catch (ValidationException $exception) {
            $origen->refresh();
            $destino->refresh();

            $this->assertSame(500, $origen->aves_actuales);
            $this->assertSame(100, $destino->aves_actuales);
            $this->assertArrayHasKey('cantidad', $exception->errors());

            throw $exception;
        }
    }

    public function test_traslado_rechaza_mismo_galpon(): void
    {
        [$encargado, $origen, $destino] = $this->contextoDosGalpones(0, 0);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $origen,
            [TipoHuevo::Blanco->value => 100],
            Carbon::today(),
        )->first();

        $this->expectException(ValidationException::class);

        try {
            app(RegistrarTrasladoAvesAction::class)->execute(
                $encargado,
                $origen,
                $origen,
                $lote,
                50,
                'Mismo galpón',
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('galponDestinoId', $exception->errors());

            throw $exception;
        }
    }

    public function test_traslado_rechaza_destino_no_disponible(): void
    {
        [$encargado, $origen, $destino] = $this->contextoDosGalpones(0, 0);

        $destino->update([
            'estado' => GalponEstado::EnMantenimiento,
            'activo' => false,
        ]);
        $destino->refresh();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $origen,
            [TipoHuevo::Blanco->value => 800],
            Carbon::today(),
        )->first();

        $this->expectException(ValidationException::class);

        try {
            app(RegistrarTrasladoAvesAction::class)->execute(
                $encargado,
                $origen,
                $destino,
                $lote,
                100,
                'Destino en mantenimiento',
            );
        } catch (ValidationException $exception) {
            $origen->refresh();
            $destino->refresh();

            $this->assertSame(800, $origen->aves_actuales);
            $this->assertSame(0, $destino->aves_actuales);
            $this->assertArrayHasKey('galponDestinoId', $exception->errors());

            throw $exception;
        }
    }

    public function test_traslado_rechaza_galpon_de_otra_empresa(): void
    {
        [$encargado, $origen, $destino] = $this->contextoDosGalpones(0, 0);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $origen,
            [TipoHuevo::Blanco->value => 600],
            Carbon::today(),
        )->first();

        $empresaAjena = Empresa::factory()->create();
        $granjaAjena = Granja::factory()->create(['empresa_id' => $empresaAjena->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create([
            'aves_actuales' => 0,
            'activo' => true,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(RegistrarTrasladoAvesAction::class)->execute(
                $encargado,
                $origen,
                $galponAjeno,
                $lote,
                100,
                'Cross empresa',
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('galponDestinoId', $exception->errors());

            throw $exception;
        }
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon}
     */
    private function contextoDosGalpones(int $avesOrigen, int $avesDestino): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $origen = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => $avesOrigen,
            'activo' => true,
        ]);
        $destino = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => $avesDestino,
            'activo' => true,
        ]);

        return [$encargado, $origen, $destino];
    }
}
