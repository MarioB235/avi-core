<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Lote\ReabrirLoteExcepcionalAction;
use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarCierreLoteAction;
use App\Enums\LoteEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Support\IdempotenciaMovimiento;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoAvesReaperturaLoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_reapertura_restaura_aves_sin_segundo_saldo_inicial(): void
    {
        [$dueno, $galpon] = $this->contextoDueno();

        $lote = app(RegistrarLoteAction::class)->execute(
            $dueno,
            $galpon,
            [TipoHuevo::Blanco->value => 600],
            Carbon::today(),
        )->first();

        app(RegistrarCierreLoteAction::class)->execute(
            $dueno,
            $galpon,
            $lote,
            600,
            'Cierre para prueba de reapertura',
        );

        $saldoInicialAntes = MovimientoAves::query()
            ->where('lote_id', $lote->id)
            ->where('metadata->origen', MovimientoAvesOrigen::SaldoInicialLote->value)
            ->count();

        $loteReabierto = app(ReabrirLoteExcepcionalAction::class)->execute(
            $dueno,
            $lote->fresh(),
            'Corrección administrativa del cierre',
        );

        $galpon->refresh();

        $this->assertSame(LoteEstado::Activo, $loteReabierto->estado);
        $this->assertSame(600, $galpon->aves_actuales);
        $this->assertSame($saldoInicialAntes, MovimientoAves::query()
            ->where('lote_id', $lote->id)
            ->where('metadata->origen', MovimientoAvesOrigen::SaldoInicialLote->value)
            ->count());
        $this->assertSame(1, MovimientoAves::query()
            ->where('lote_id', $lote->id)
            ->where('metadata->origen', MovimientoAvesOrigen::ReaperturaLote->value)
            ->count());
    }

    public function test_encargado_no_puede_reabrir_lote_cerrado(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        $lote = $this->loteCerrado($encargado, $galpon, 200);

        $this->expectException(AuthorizationException::class);

        app(ReabrirLoteExcepcionalAction::class)->execute(
            $encargado,
            $lote,
            'Intento encargado',
        );
    }

    public function test_reapertura_rechaza_galpon_con_otro_lote_activo(): void
    {
        [$dueno, $galpon] = $this->contextoDueno();

        $loteCerrado = $this->loteCerrado($dueno, $galpon, 150);

        Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 400,
            'estado' => LoteEstado::Activo,
        ]);

        $galpon->update(['aves_actuales' => 400]);

        $this->expectException(ValidationException::class);

        try {
            app(ReabrirLoteExcepcionalAction::class)->execute(
                $dueno,
                $loteCerrado,
                'Conflicto con ciclo nuevo',
            );
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('lote_id', $exception->errors());

            throw $exception;
        }
    }

    public function test_reapertura_idempotente_no_duplica_restauracion(): void
    {
        [$dueno, $galpon] = $this->contextoDueno();
        $lote = $this->loteCerrado($dueno, $galpon, 250);

        $action = app(ReabrirLoteExcepcionalAction::class);

        $action->execute($dueno, $lote, 'Primera reapertura válida');
        $action->execute($dueno, $lote->fresh(), 'Reintento idempotente');

        $galpon->refresh();

        $this->assertSame(250, $galpon->aves_actuales);
        $this->assertSame(1, MovimientoAves::query()
            ->where('idempotencia_clave', IdempotenciaMovimiento::claveReaperturaLote($lote->id))
            ->count());
    }

    public function test_reapertura_sin_cierre_previo_solo_cambia_estado(): void
    {
        [$dueno, $galpon] = $this->contextoDueno();

        $lote = Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 100,
            'estado' => LoteEstado::Cerrado,
        ]);

        $galpon->update(['aves_actuales' => 0]);

        $reabierto = app(ReabrirLoteExcepcionalAction::class)->execute(
            $dueno,
            $lote,
            'Reapertura sin movimiento de cierre',
        );

        $galpon->refresh();

        $this->assertSame(LoteEstado::Activo, $reabierto->estado);
        $this->assertSame(0, $galpon->aves_actuales);
        $this->assertSame(0, MovimientoAves::query()
            ->where('metadata->origen', MovimientoAvesOrigen::ReaperturaLote->value)
            ->count());
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function contextoDueno(): array
    {
        $empresa = Empresa::factory()->create();
        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => 0,
            'activo' => true,
        ]);

        return [$dueno, $galpon];
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

    private function loteCerrado(User $actor, Galpon $galpon, int $cantidad): Lote
    {
        $lote = app(RegistrarLoteAction::class)->execute(
            $actor,
            $galpon,
            [TipoHuevo::Blanco->value => $cantidad],
            Carbon::today(),
        )->first();

        app(RegistrarCierreLoteAction::class)->execute(
            $actor,
            $galpon,
            $lote,
            $cantidad,
            'Cierre de prueba',
        );

        return $lote->fresh();
    }
}
