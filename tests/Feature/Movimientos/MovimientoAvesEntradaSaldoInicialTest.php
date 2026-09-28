<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Empresa\StartSoporteEmpresaAction;
use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarEntradaAvesAction;
use App\Actions\Movimiento\RegistrarSaldoInicialLoteAction;
use App\Enums\MovimientoAvesOrigen;
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
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoAvesEntradaSaldoInicialTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_lote_crea_movimiento_saldo_inicial_sin_duplicar_aves(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lotes = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 2500],
            Carbon::parse('2026-03-01'),
        );

        $galpon->refresh();
        $lote = $lotes->first();

        $this->assertSame(2500, $galpon->aves_actuales);
        $this->assertSame(1, MovimientoAves::query()->where('lote_id', $lote->id)->count());

        $movimiento = MovimientoAves::query()->where('lote_id', $lote->id)->first();

        $this->assertSame(MovimientoAvesTipo::Entrada, $movimiento->tipo);
        $this->assertSame(MovimientoAvesOrigen::SaldoInicialLote->value, $movimiento->metadata['origen']);
        $this->assertFalse($movimiento->metadata['impacta_aves_actuales']);
        $this->assertSame(
            IdempotenciaMovimiento::claveSaldoInicialLote($lote->id),
            $movimiento->idempotencia_clave,
        );
    }

    public function test_reintento_saldo_inicial_no_duplica_movimiento_ni_aves(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 1800,
            'fecha_ingreso' => now(),
        ]);

        $galpon->update(['aves_actuales' => 1800]);

        $action = app(RegistrarSaldoInicialLoteAction::class);

        $primero = $action->execute($encargado, $lote, $galpon, 1800);
        $segundo = $action->execute($encargado, $lote, $galpon, 1800);

        $galpon->refresh();

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1, MovimientoAves::query()->where('lote_id', $lote->id)->count());
        $this->assertSame(1800, $galpon->aves_actuales);
    }

    public function test_entrada_externa_idempotente_suma_aves_una_sola_vez(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = Lote::factory()->forGalpon($galpon)->create(['cantidad_inicial' => 0]);
        $galpon->update(['aves_actuales' => 0]);

        $clave = IdempotenciaCaptura::generarClave();
        $action = app(RegistrarEntradaAvesAction::class);

        $primero = $action->execute($encargado, $galpon, $lote, 350, 'Compra externa', $clave);
        $segundo = $action->execute($encargado, $galpon, $lote, 350, 'Compra externa', $clave);

        $galpon->refresh();

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1, MovimientoAves::query()->where('idempotencia_clave', $clave)->count());
        $this->assertSame(350, $galpon->aves_actuales);
        $this->assertSame(MovimientoAvesOrigen::EntradaExterna->value, $primero->metadata['origen']);
    }

    public function test_ledger_incluye_saldo_inicial_y_entrada_externa(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lotes = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 1000],
            Carbon::today(),
        );

        $lote = $lotes->first();

        app(RegistrarEntradaAvesAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            200,
            'Ingreso adicional',
            IdempotenciaCaptura::generarClave(),
        );

        $movimientos = MovimientoAves::query()->where('galpon_destino_id', $galpon->id)->get();
        $saldo = MovimientoAvesEfecto::saldoNetoEnGalpon($movimientos, $galpon->id);

        $this->assertSame(1200, $saldo);
        $galpon->refresh();
        $this->assertSame(1200, $galpon->aves_actuales);
    }

    public function test_operario_no_puede_registrar_entrada_externa(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();
        $lote = Lote::factory()->forGalpon($galpon)->create(['cantidad_inicial' => 0]);

        $operario = User::factory()->create([
            'empresa_id' => $galpon->empresa_id,
            'rol' => UserRole::Operario,
        ]);

        $this->expectException(AuthorizationException::class);

        app(RegistrarEntradaAvesAction::class)->execute(
            $operario,
            $galpon,
            $lote,
            100,
            'Intento operario',
            IdempotenciaCaptura::generarClave(),
        );
    }

    public function test_entrada_externa_rechaza_galpon_de_otra_empresa(): void
    {
        [$encargado, $galponPropio] = $this->contextoEncargado();
        $lotePropio = Lote::factory()->forGalpon($galponPropio)->create(['cantidad_inicial' => 0]);

        $empresaAjena = Empresa::factory()->create();
        $granjaAjena = Granja::factory()->create(['empresa_id' => $empresaAjena->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create(['aves_actuales' => 0]);

        $this->expectException(ValidationException::class);

        try {
            app(RegistrarEntradaAvesAction::class)->execute(
                $encargado,
                $galponAjeno,
                $lotePropio,
                50,
                'Intento cross-empresa',
                IdempotenciaCaptura::generarClave(),
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('galponId', $exception->errors());

            throw $exception;
        }
    }

    public function test_admin_en_soporte_no_puede_registrar_entrada_externa(): void
    {
        $admin = User::factory()->adminAvicore()->create([
            'must_change_password' => false,
        ]);

        $empresa = Empresa::factory()->create();
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0, 'activo' => true]);
        $lote = Lote::factory()->forGalpon($galpon)->create(['cantidad_inicial' => 0]);

        app(StartSoporteEmpresaAction::class)->execute($admin, $empresa, [
            'motivo' => 'Revisión operativa solicitada por el cliente.',
        ]);

        $this->expectException(AuthorizationException::class);

        app(RegistrarEntradaAvesAction::class)->execute(
            $admin,
            $galpon,
            $lote,
            100,
            'Intento soporte',
            IdempotenciaCaptura::generarClave(),
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
