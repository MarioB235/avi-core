<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarFaenaAction;
use App\Enums\LoteEstado;
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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoAvesFaenaTest extends TestCase
{
    use RefreshDatabase;

    public function test_faena_cierre_ciclo_registra_metadata_y_cierra_lote(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 900],
            Carbon::today(),
        )->first();

        $galpon->refresh();

        $movimiento = app(RegistrarFaenaAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            900,
            'Fin de ciclo — envío a planta',
            'Frigorífico Norte',
            true,
            'REM-2026-0042',
            'Guía interna 18',
        );

        $galpon->refresh();
        $lote->refresh();

        $this->assertSame(MovimientoAvesTipo::Faena, $movimiento->tipo);
        $this->assertSame(LoteEstado::Cerrado, $lote->estado);
        $this->assertSame(0, $galpon->aves_actuales);
        $this->assertSame(MovimientoAvesOrigen::FaenaOperativa->value, $movimiento->metadata['origen']);
        $this->assertSame('Frigorífico Norte', $movimiento->metadata['destino_faena']);
        $this->assertSame('REM-2026-0042', $movimiento->metadata['referencia_remito']);
        $this->assertSame('Guía interna 18', $movimiento->metadata['referencia_documento']);
        $this->assertTrue($movimiento->metadata['trazabilidad_interna']);
    }

    public function test_faena_parcial_no_cierra_lote(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 600],
            Carbon::today(),
        )->first();

        $galpon->refresh();

        app(RegistrarFaenaAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            150,
            'Salida parcial a faena',
            'Planta regional',
            false,
        );

        $galpon->refresh();
        $lote->refresh();

        $this->assertSame(LoteEstado::Activo, $lote->estado);
        $this->assertSame(450, $galpon->aves_actuales);
    }

    public function test_faena_rechaza_destino_vacio(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 100],
            Carbon::today(),
        )->first();

        $this->expectException(ValidationException::class);

        app(RegistrarFaenaAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            50,
            'Motivo válido',
            '   ',
        );
    }

    public function test_faena_idempotente_no_duplica_efecto(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 200],
            Carbon::today(),
        )->first();

        $galpon->refresh();
        $clave = IdempotenciaCaptura::generarClave();
        $action = app(RegistrarFaenaAction::class);

        $primero = $action->execute($encargado, $galpon, $lote, 200, 'Faena', 'Planta A', true, null, null, null, null, $clave);
        $segundo = $action->execute($encargado, $galpon, $lote, 200, 'Faena', 'Planta A', true, null, null, null, null, $clave);

        $galpon->refresh();

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(0, $galpon->aves_actuales);
    }

    public function test_operario_no_puede_registrar_faena(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 80],
            Carbon::today(),
        )->first();

        $operario = User::factory()->create([
            'empresa_id' => $galpon->empresa_id,
            'rol' => UserRole::Operario,
        ]);

        $this->expectException(AuthorizationException::class);

        app(RegistrarFaenaAction::class)->execute(
            $operario,
            $galpon,
            $lote,
            80,
            'Intento operario',
            'Planta X',
        );
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
}
