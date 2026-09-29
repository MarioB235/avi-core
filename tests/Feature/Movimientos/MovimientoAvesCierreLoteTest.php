<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarCierreLoteAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Enums\LoteEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Support\IdempotenciaCaptura;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoAvesCierreLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_cierre_ciclo_deja_galpon_sin_aves_y_lote_cerrado(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 1200],
            Carbon::today(),
        )->first();

        $galpon->refresh();

        $movimiento = app(RegistrarCierreLoteAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            1200,
            'Fin de ciclo productivo',
            'Faena interna',
        );

        $galpon->refresh();
        $lote->refresh();

        $this->assertSame(MovimientoAvesTipo::CierreLote, $movimiento->tipo);
        $this->assertSame(LoteEstado::Cerrado, $lote->estado);
        $this->assertSame(0, $galpon->aves_actuales);
        $this->assertSame(MovimientoAvesOrigen::CierreLoteOperativo->value, $movimiento->metadata['origen']);
        $this->assertSame('Faena interna', $movimiento->metadata['destino_salida']);
    }

    public function test_cierre_parcial_no_transiciona_lote(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 800],
            Carbon::today(),
        )->first();

        $galpon->refresh();

        app(RegistrarCierreLoteAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            200,
            'Salida parcial',
            null,
            false,
        );

        $galpon->refresh();
        $lote->refresh();

        $this->assertSame(LoteEstado::Activo, $lote->estado);
        $this->assertSame(600, $galpon->aves_actuales);
    }

    public function test_cierre_ciclo_rechaza_remanente_incompleto(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 500],
            Carbon::today(),
        )->first();

        $galpon->refresh();

        $this->expectException(ValidationException::class);

        try {
            app(RegistrarCierreLoteAction::class)->execute(
                $encargado,
                $galpon,
                $lote,
                400,
                'Cierre incompleto',
            );
        } catch (ValidationException $exception) {
            $galpon->refresh();
            $lote->refresh();

            $this->assertSame(500, $galpon->aves_actuales);
            $this->assertSame(LoteEstado::Activo, $lote->estado);
            $this->assertArrayHasKey('cantidad', $exception->errors());

            throw $exception;
        }
    }

    public function test_tras_cierre_ciclo_no_permite_carga_productiva(): void
    {
        [$encargado, $galpon, $operario] = $this->contextoConOperario();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 150],
            Carbon::today(),
        )->first();

        $galpon->refresh();

        app(RegistrarCierreLoteAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            150,
            'Cierre para sanitización',
        );

        $this->expectException(ValidationException::class);

        app(RegistrarCargaMuertesAction::class)->execute($operario, $galpon, 1);
    }

    public function test_cierre_idempotente_no_duplica_efecto(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 300],
            Carbon::today(),
        )->first();

        $galpon->refresh();
        $clave = IdempotenciaCaptura::generarClave();
        $action = app(RegistrarCierreLoteAction::class);

        $primero = $action->execute($encargado, $galpon, $lote, 300, 'Cierre', null, true, null, null, $clave);
        $segundo = $action->execute($encargado, $galpon, $lote, 300, 'Cierre', null, true, null, null, $clave);

        $galpon->refresh();
        $lote->refresh();

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(0, $galpon->aves_actuales);
        $this->assertSame(LoteEstado::Cerrado, $lote->estado);
    }

    public function test_operario_no_puede_cerrar_lote(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 100],
            Carbon::today(),
        )->first();

        $operario = User::factory()->create([
            'empresa_id' => $galpon->empresa_id,
            'rol' => UserRole::Operario,
        ]);

        $this->expectException(AuthorizationException::class);

        app(RegistrarCierreLoteAction::class)->execute(
            $operario,
            $galpon,
            $lote,
            100,
            'Intento operario',
        );
    }

    public function test_cierre_con_dos_lotes_exige_imputacion(): void
    {
        [$encargado, $galpon, $loteA, $loteB] = $this->galponConDosLotes();

        $this->expectException(ValidationException::class);

        try {
            app(RegistrarCierreLoteAction::class)->execute(
                $encargado,
                $galpon,
                $loteA,
                500,
                'Cierre sin conciliar',
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('muertes_imputadas_lote', $exception->errors());

            throw $exception;
        }
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
     * @return array{0: User, 1: Galpon, 2: Lote, 3: Lote}
     */
    private function galponConDosLotes(): array
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $loteA = Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 3000,
            'estado' => LoteEstado::Activo,
        ]);
        $loteB = Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 2800,
            'estado' => LoteEstado::Activo,
        ]);

        $galpon->update(['aves_actuales' => 5800]);

        return [$encargado, $galpon, $loteA, $loteB];
    }
}
