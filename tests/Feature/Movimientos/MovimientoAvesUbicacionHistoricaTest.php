<?php

namespace Tests\Feature\Movimientos;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarTrasladoAvesAction;
use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Services\LoteUbicacionHistoricaService;
use App\Support\IdempotenciaCaptura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MovimientoAvesUbicacionHistoricaTest extends TestCase
{
    use RefreshDatabase;

    public function test_traslado_parcial_no_cambia_expediente_ni_ubicacion_historica(): void
    {
        [$encargado, $origen, $destino, $lote] = $this->contextoConLote(3000);

        $antes = now()->subDay();
        $this->assertSame($origen->id, app(LoteUbicacionHistoricaService::class)->galponEnMomento($lote, $antes));

        app(RegistrarTrasladoAvesAction::class)->execute(
            $encargado,
            $origen,
            $destino,
            $lote,
            800,
            'Traslado parcial',
        );

        $lote->refresh();
        $ubicacion = app(LoteUbicacionHistoricaService::class);

        $this->assertSame($origen->id, $lote->galpon_id);
        $this->assertSame($origen->id, $ubicacion->galponEnMomento($lote, now()));
        $this->assertSame($origen->id, $ubicacion->galponEnMomento($lote, $antes));
    }

    public function test_traslado_total_reasigna_expediente_y_segmentos(): void
    {
        [$encargado, $origen, $destino, $lote] = $this->contextoConLote(1500);

        $momentoTraslado = Carbon::parse('2026-04-10 10:00:00');

        app(RegistrarTrasladoAvesAction::class)->execute(
            $encargado,
            $origen,
            $destino,
            $lote,
            1500,
            'Traslado total del lote',
            fechaEfectiva: $momentoTraslado,
        );

        $lote->refresh();
        $ubicacion = app(LoteUbicacionHistoricaService::class);

        $this->assertSame($destino->id, $lote->galpon_id);
        $this->assertSame($origen->id, $ubicacion->galponEnMomento($lote, $momentoTraslado->copy()->subHour()));
        $this->assertSame($destino->id, $ubicacion->galponEnMomento($lote, $momentoTraslado->copy()->addHour()));

        $segmentos = $ubicacion->segmentosUbicacion($lote);
        $this->assertCount(2, $segmentos);
        $this->assertSame($origen->id, $segmentos[0]['galpon_id']);
        $this->assertSame('2026-04-10', $segmentos[0]['hasta']);
        $this->assertSame($destino->id, $segmentos[1]['galpon_id']);
        $this->assertNull($segmentos[1]['hasta']);
    }

    public function test_produccion_previa_no_migra_al_galpon_destino_tras_traslado_total(): void
    {
        [$encargado, $origen, $destino, $lote, $operario] = $this->contextoConLoteYOperario(1200);

        $registro = app(RegistrarCargaHuevosAction::class)->execute(
            $operario,
            $origen,
            340,
            0,
            null,
            IdempotenciaCaptura::generarClave(),
        );

        $momentoCaptura = $registro->created_at->copy();

        $momentoTraslado = $momentoCaptura->copy()->addHour();

        app(RegistrarTrasladoAvesAction::class)->execute(
            $encargado,
            $origen,
            $destino,
            $lote,
            1200,
            'Traslado total',
            fechaEfectiva: $momentoTraslado,
        );

        $lote->refresh();

        $ubicacion = app(LoteUbicacionHistoricaService::class);
        $dia = $momentoCaptura->copy()->startOfDay();

        $this->assertSame($origen->id, $ubicacion->galponDelHechoOperativo($registro));
        $this->assertSame(340, $ubicacion->huevosAptosPorGalponEnPeriodo($origen, $dia, $dia));
        $this->assertSame(0, $ubicacion->huevosAptosPorGalponEnPeriodo($destino, $dia, $dia));
        $this->assertSame($origen->id, $ubicacion->galponEnMomento($lote, $momentoCaptura));
        $this->assertSame($destino->id, $ubicacion->galponEnMomento($lote, $momentoTraslado->copy()->addMinute()));
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon, 3: Lote}
     */
    private function contextoConLote(int $cantidad): array
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
            [TipoHuevo::Blanco->value => $cantidad],
            Carbon::parse('2026-03-01'),
        )->first();

        $origen->refresh();
        $destino->refresh();
        $lote->refresh();

        return [$encargado, $origen, $destino, $lote];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon, 3: Lote, 4: User}
     */
    private function contextoConLoteYOperario(int $cantidad): array
    {
        [$encargado, $origen, $destino, $lote] = $this->contextoConLote($cantidad);
        $operario = User::factory()->create([
            'empresa_id' => $origen->empresa_id,
            'rol' => UserRole::Operario,
        ]);

        return [$encargado, $origen, $destino, $lote, $operario];
    }
}
