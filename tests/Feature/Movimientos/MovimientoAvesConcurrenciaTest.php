<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarTrasladoAvesAction;
use App\Enums\LoteEstado;
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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\RunsMovimientoConcurrentWorkers;
use Tests\TestCase;

class MovimientoAvesConcurrenciaTest extends TestCase
{
    use RefreshDatabase;
    use RunsMovimientoConcurrentWorkers;

    /**
     * Sin transacción envolvente: los workers usan otra conexión PostgreSQL al mismo dataset.
     *
     * @var list<string|null>
     */
    protected array $connectionsToTransact = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('MOV-11 requiere PostgreSQL (dos sesiones reales).');
        }
    }

    public function test_dos_traslados_concurrentes_no_superan_saldo_ni_corrompen_total(): void
    {
        [$encargado, $origen, $destinoA, $destinoB, $lote] = $this->contextoTraslado(1000);

        $totalAntes = $this->totalAvesEmpresa($origen->empresa_id);

        $results = $this->runMovimientoWorkersInParallel([
            [
                'action' => 'traslado',
                'user_id' => $encargado->id,
                'params' => [
                    'origen_id' => $origen->id,
                    'destino_id' => $destinoA->id,
                    'lote_id' => $lote->id,
                    'cantidad' => 700,
                    'motivo' => 'Traslado concurrente A',
                ],
            ],
            [
                'action' => 'traslado',
                'user_id' => $encargado->id,
                'params' => [
                    'origen_id' => $origen->id,
                    'destino_id' => $destinoB->id,
                    'lote_id' => $lote->id,
                    'cantidad' => 700,
                    'motivo' => 'Traslado concurrente B',
                ],
            ],
        ]);

        $exitos = array_filter($results, fn (array $r): bool => $r['ok'] === true);

        $this->assertCount(1, $exitos);
        $this->assertCount(1, array_filter($results, fn (array $r): bool => $r['ok'] === false));

        $origen->refresh();
        $destinoA->refresh();
        $destinoB->refresh();

        $this->assertSame($totalAntes, $this->totalAvesEmpresa($origen->empresa_id));
        $this->assertGreaterThanOrEqual(0, $origen->aves_actuales);
        $this->assertSame(1000, $origen->aves_actuales + $destinoA->aves_actuales + $destinoB->aves_actuales);
    }

    public function test_doble_cierre_concurrente_solo_uno_cierra_ciclo(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoUnGalpon(800);

        $results = $this->runMovimientoWorkersInParallel([
            [
                'action' => 'cierre',
                'user_id' => $encargado->id,
                'params' => [
                    'galpon_id' => $galpon->id,
                    'lote_id' => $lote->id,
                    'cantidad' => 800,
                    'motivo' => 'Cierre concurrente 1',
                    'cerrar_ciclo' => true,
                ],
            ],
            [
                'action' => 'cierre',
                'user_id' => $encargado->id,
                'params' => [
                    'galpon_id' => $galpon->id,
                    'lote_id' => $lote->id,
                    'cantidad' => 800,
                    'motivo' => 'Cierre concurrente 2',
                    'cerrar_ciclo' => true,
                ],
            ],
        ]);

        $exitos = array_filter($results, fn (array $r): bool => $r['ok'] === true);
        $this->assertCount(1, $exitos);

        $lote->refresh();
        $galpon->refresh();

        $this->assertSame(LoteEstado::Cerrado, $lote->estado);
        $this->assertSame(0, $galpon->aves_actuales);
        $this->assertSame(
            1,
            MovimientoAves::query()
                ->where('lote_id', $lote->id)
                ->where('tipo', MovimientoAvesTipo::CierreLote)
                ->where('estado', MovimientoAvesEstado::Activo->value)
                ->count(),
        );
    }

    public function test_muertes_y_traslado_concurrentes_conservan_saldo_no_negativo(): void
    {
        [$encargado, $operario, $origen, $destino, $lote] = $this->contextoTrasladoConOperario(1000);

        $results = $this->runMovimientoWorkersInParallel([
            [
                'action' => 'muertes',
                'user_id' => $operario->id,
                'params' => [
                    'galpon_id' => $origen->id,
                    'muertes' => 600,
                ],
            ],
            [
                'action' => 'traslado',
                'user_id' => $encargado->id,
                'params' => [
                    'origen_id' => $origen->id,
                    'destino_id' => $destino->id,
                    'lote_id' => $lote->id,
                    'cantidad' => 900,
                    'motivo' => 'Traslado concurrente vs muertes',
                ],
            ],
        ]);

        $this->assertCount(1, array_filter($results, fn (array $r): bool => $r['ok'] === true));
        $this->assertCount(1, array_filter($results, fn (array $r): bool => $r['ok'] === false));

        $origen->refresh();
        $destino->refresh();

        $this->assertGreaterThanOrEqual(0, $origen->aves_actuales);
        $this->assertGreaterThanOrEqual(0, $destino->aves_actuales);
        $this->assertLessThanOrEqual(
            1000,
            $origen->aves_actuales + $destino->aves_actuales,
        );
    }

    public function test_segunda_conexion_espera_lock_o_falla_sin_corromper_saldo(): void
    {
        [$encargado, $origen, $destino, , $lote] = $this->contextoTraslado(500);

        $this->registerSesionPgsqlAlterna();
        $sesionBloqueo = DB::connection('pgsql_concurrent');

        $sesionBloqueo->beginTransaction();
        $sesionBloqueo->table('galpones')
            ->whereIn('id', [$origen->id, $destino->id])
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        DB::connection()->statement('SET lock_timeout = 1500');

        try {
            app(RegistrarTrasladoAvesAction::class)->execute(
                $encargado,
                $origen,
                $destino,
                $lote,
                100,
                'Traslado bajo lock externo',
            );
            $this->fail('Se esperaba fallo por lock_timeout o validación bajo contención.');
        } catch (ValidationException $exception) {
            $this->assertTrue(true);
        } catch (QueryException $exception) {
            $mensaje = strtolower($exception->getMessage());
            $this->assertTrue(
                str_contains($mensaje, 'lock timeout')
                || str_contains($mensaje, '55p03')
                || str_contains($mensaje, 'locks'),
                $exception->getMessage(),
            );
        } finally {
            $sesionBloqueo->rollBack();
            DB::connection()->statement('RESET lock_timeout');
        }

        $origen->refresh();
        $destino->refresh();

        $this->assertSame(500, $origen->aves_actuales);
        $this->assertSame(0, $destino->aves_actuales);
    }

    private function registerSesionPgsqlAlterna(): void
    {
        $name = 'pgsql_concurrent';
        config([
            "database.connections.{$name}" => array_merge(
                config('database.connections.'.config('database.default')),
                ['name' => $name],
            ),
        ]);
        DB::purge($name);
    }

    private function totalAvesEmpresa(int $empresaId): int
    {
        return (int) Galpon::query()
            ->whereHas('granja', fn ($q) => $q->where('empresa_id', $empresaId))
            ->sum('aves_actuales');
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon, 3: Galpon, 4: Lote}
     */
    private function contextoTraslado(int $cantidad): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $origen = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0, 'activo' => true]);
        $destinoA = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0, 'activo' => true]);
        $destinoB = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0, 'activo' => true]);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $origen,
            [TipoHuevo::Blanco->value => $cantidad],
            Carbon::today(),
        )->first();

        $origen->refresh();

        return [$encargado, $origen, $destinoA, $destinoB, $lote];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function contextoUnGalpon(int $cantidad): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0, 'activo' => true]);

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
     * @return array{0: User, 1: User, 2: Galpon, 3: Galpon, 4: Lote}
     */
    private function contextoTrasladoConOperario(int $cantidad): array
    {
        [$encargado, $origen, $destino, , $lote] = $this->contextoTraslado($cantidad);
        $operario = User::factory()->create([
            'empresa_id' => $origen->empresa_id,
            'rol' => UserRole::Operario,
        ]);

        return [$encargado, $operario, $origen, $destino, $lote];
    }
}
