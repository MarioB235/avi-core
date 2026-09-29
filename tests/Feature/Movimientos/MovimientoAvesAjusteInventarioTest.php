<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarAjusteInventarioAvesAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\IdempotenciaCaptura;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MovimientoAvesAjusteInventarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajuste_positivo_alinea_conteo_con_sistema(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 1000],
            Carbon::today(),
        );

        $galpon->refresh();
        $this->assertSame(1000, $galpon->aves_actuales);

        $movimiento = app(RegistrarAjusteInventarioAvesAction::class)->execute(
            $encargado,
            $galpon,
            985,
            'Conteo físico en galpón',
        );

        $galpon->refresh();

        $this->assertSame(MovimientoAvesTipo::Ajuste, $movimiento->tipo);
        $this->assertSame(-15, $movimiento->ajuste_delta);
        $this->assertSame(985, $galpon->aves_actuales);
        $this->assertSame(MovimientoAvesOrigen::AjusteInventario->value, $movimiento->metadata['origen']);
        $this->assertSame(1000, $movimiento->metadata['saldo_sistema_antes']);
    }

    public function test_ajuste_negativo_suma_aves_sin_tocar_muertes(): void
    {
        [$encargado, $galpon, $operario] = $this->contextoConOperario();

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 500],
            Carbon::today(),
        );

        $galpon->refresh();

        app(RegistrarCargaMuertesAction::class)->execute($operario, $galpon, 12);

        $galpon->refresh();
        $muertesAntes = RegistroOperativo::query()->where('galpon_id', $galpon->id)->count();
        $this->assertSame(488, $galpon->aves_actuales);

        app(RegistrarAjusteInventarioAvesAction::class)->execute(
            $encargado,
            $galpon,
            495,
            'Reconteo sin corregir mortalidad',
        );

        $galpon->refresh();
        $muertesDespues = RegistroOperativo::query()->where('galpon_id', $galpon->id)->count();

        $this->assertSame($muertesAntes, $muertesDespues);
        $this->assertSame(495, $galpon->aves_actuales);
    }

    public function test_ajuste_idempotente_no_duplica_efecto(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 200],
            Carbon::today(),
        );

        $galpon->refresh();
        $clave = IdempotenciaCaptura::generarClave();
        $action = app(RegistrarAjusteInventarioAvesAction::class);

        $primero = $action->execute($encargado, $galpon, 190, 'Conteo', $clave);
        $segundo = $action->execute($encargado, $galpon, 190, 'Conteo', $clave);

        $galpon->refresh();

        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(190, $galpon->aves_actuales);
    }

    public function test_rechaza_conteo_igual_al_sistema(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 300],
            Carbon::today(),
        );

        $galpon->refresh();

        $this->expectException(ValidationException::class);

        try {
            app(RegistrarAjusteInventarioAvesAction::class)->execute(
                $encargado,
                $galpon,
                300,
                'Sin diferencia',
            );
        } catch (ValidationException $exception) {
            $galpon->refresh();
            $this->assertSame(300, $galpon->aves_actuales);
            $this->assertArrayHasKey('conteoFisico', $exception->errors());

            throw $exception;
        }
    }

    public function test_rechaza_conteo_fisico_negativo_sin_mutar_saldo(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 80],
            Carbon::today(),
        );

        $galpon->refresh();

        $this->expectException(ValidationException::class);

        try {
            app(RegistrarAjusteInventarioAvesAction::class)->execute(
                $encargado,
                $galpon,
                -1,
                'Conteo inválido',
            );
        } catch (ValidationException $exception) {
            $galpon->refresh();
            $this->assertSame(80, $galpon->aves_actuales);
            $this->assertArrayHasKey('conteoFisico', $exception->errors());

            throw $exception;
        }
    }

    public function test_operario_no_puede_ajustar_inventario(): void
    {
        [$encargado, $galpon] = $this->contextoEncargado();

        app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 100],
            Carbon::today(),
        );

        $operario = User::factory()->create([
            'empresa_id' => $galpon->empresa_id,
            'rol' => UserRole::Operario,
        ]);

        $this->expectException(AuthorizationException::class);

        app(RegistrarAjusteInventarioAvesAction::class)->execute(
            $operario,
            $galpon,
            90,
            'Intento operario',
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
}
