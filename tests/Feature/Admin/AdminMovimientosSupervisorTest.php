<?php

namespace Tests\Feature\Admin;

use App\Actions\Lote\RegistrarLoteAction;
use App\Enums\LoteEstado;
use App\Enums\MovimientoAvesOrigen;
use App\Enums\MovimientoAvesTipo;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Livewire\Admin\Movimientos\Index as MovimientosIndex;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Services\MovimientoAvesVistaPreviaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMovimientosSupervisorTest extends TestCase
{
    use RefreshDatabase;

    public function test_encargado_ve_vista_previa_y_registra_traslado_con_motivo(): void
    {
        [$encargado, $origen, $destino, $lote] = $this->contextoTraslado();

        $this->assertTrue(Gate::forUser($encargado)->allows('admin.viewMovimientos'));

        Livewire::actingAs($encargado)
            ->test(MovimientosIndex::class)
            ->assertSee('Vista previa del efecto', false)
            ->set('galponOrigenId', (string) $origen->id)
            ->set('galponDestinoId', (string) $destino->id)
            ->set('loteId', (string) $lote->id)
            ->set('cantidad', '200')
            ->assertSee('sin cambio', false)
            ->set('motivo', 'Traslado por capacidad en galpón')
            ->call('abrirConfirmacion')
            ->assertSet('dialogConfirmarAbierto', true)
            ->call('ejecutarMovimiento')
            ->assertSet('dialogConfirmarAbierto', false);

        $origen->refresh();
        $destino->refresh();

        $this->assertSame(800, $origen->aves_actuales);
        $this->assertSame(200, $destino->aves_actuales);
        $this->assertSame(1, MovimientoAves::query()->where('tipo', 'traslado')->count());
    }

    public function test_operario_no_accede_a_pantalla_movimientos(): void
    {
        $empresa = Empresa::factory()->create();
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->assertFalse(Gate::forUser($operario)->allows('admin.viewMovimientos'));

        Livewire::actingAs($operario)
            ->test(MovimientosIndex::class)
            ->assertForbidden();
    }

    public function test_sin_motivo_no_abre_confirmacion(): void
    {
        [$encargado, $origen, $destino, $lote] = $this->contextoTraslado();

        Livewire::actingAs($encargado)
            ->test(MovimientosIndex::class)
            ->set('galponOrigenId', (string) $origen->id)
            ->set('galponDestinoId', (string) $destino->id)
            ->set('loteId', (string) $lote->id)
            ->set('cantidad', '100')
            ->set('motivo', '')
            ->call('abrirConfirmacion')
            ->assertHasErrors(['motivo']);
    }

    public function test_livewire_registra_entrada_externa(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoGalponConLote(500);

        Livewire::actingAs($encargado)
            ->test(MovimientosIndex::class)
            ->set('tipo', 'entrada')
            ->set('galponId', (string) $galpon->id)
            ->set('loteId', (string) $lote->id)
            ->set('cantidad', '120')
            ->set('motivo', 'Compra externa de recría')
            ->call('abrirConfirmacion')
            ->call('ejecutarMovimiento');

        $galpon->refresh();
        $this->assertSame(620, $galpon->aves_actuales);
        $this->assertSame(1, MovimientoAves::query()
            ->where('tipo', MovimientoAvesTipo::Entrada)
            ->where('metadata->origen', MovimientoAvesOrigen::EntradaExterna->value)
            ->count());
    }

    public function test_livewire_registra_ajuste_inventario(): void
    {
        [$encargado, $galpon] = $this->contextoGalponConLote(800);

        Livewire::actingAs($encargado)
            ->test(MovimientosIndex::class)
            ->set('tipo', 'ajuste')
            ->set('galponId', (string) $galpon->id)
            ->set('conteoFisico', '790')
            ->set('motivo', 'Conteo físico en galpón')
            ->call('abrirConfirmacion')
            ->call('ejecutarMovimiento');

        $galpon->refresh();
        $this->assertSame(790, $galpon->aves_actuales);
        $this->assertSame(1, MovimientoAves::query()->where('tipo', MovimientoAvesTipo::Ajuste)->count());
    }

    public function test_livewire_registra_cierre_parcial_de_lote(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoGalponConLote(600);

        Livewire::actingAs($encargado)
            ->test(MovimientosIndex::class)
            ->set('tipo', 'cierre')
            ->set('galponId', (string) $galpon->id)
            ->set('loteId', (string) $lote->id)
            ->set('cantidad', '150')
            ->set('cerrarCicloLote', false)
            ->set('motivo', 'Salida parcial del lote')
            ->call('abrirConfirmacion')
            ->call('ejecutarMovimiento');

        $galpon->refresh();
        $lote->refresh();
        $this->assertSame(450, $galpon->aves_actuales);
        $this->assertSame(LoteEstado::Activo, $lote->estado);
    }

    public function test_livewire_registra_faena_parcial(): void
    {
        [$encargado, $galpon, $lote] = $this->contextoGalponConLote(700);

        Livewire::actingAs($encargado)
            ->test(MovimientosIndex::class)
            ->set('tipo', 'faena')
            ->set('galponId', (string) $galpon->id)
            ->set('loteId', (string) $lote->id)
            ->set('cantidad', '200')
            ->set('destinoFaena', 'Planta frigorífica regional')
            ->set('cerrarCicloLote', false)
            ->set('motivo', 'Envío parcial a faena')
            ->call('abrirConfirmacion')
            ->call('ejecutarMovimiento');

        $galpon->refresh();
        $lote->refresh();
        $this->assertSame(500, $galpon->aves_actuales);
        $this->assertSame(LoteEstado::Activo, $lote->estado);
        $this->assertSame(1, MovimientoAves::query()->where('tipo', MovimientoAvesTipo::Faena)->count());
    }

    public function test_servicio_vista_previa_rechaza_traslado_por_saldo(): void
    {
        [$encargado, $origen, $destino] = $this->contextoTraslado(sinLoteExtra: true);
        unset($encargado);

        $preview = app(MovimientoAvesVistaPreviaService::class)->traslado($origen, $destino, 5000);

        $this->assertFalse($preview['valido']);
        $this->assertNotEmpty($preview['errores']);
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon, 3: Lote}
     */
    private function contextoTraslado(bool $sinLoteExtra = false): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $origen = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0, 'activo' => true]);
        $destino = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0, 'activo' => true]);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $origen,
            [TipoHuevo::Blanco->value => 1000],
            Carbon::today(),
        )->first();

        $origen->refresh();
        $destino->refresh();

        if ($sinLoteExtra) {
            return [$encargado, $origen, $destino, $lote];
        }

        return [$encargado, $origen, $destino, $lote];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function contextoGalponConLote(int $cantidadLote): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 0, 'activo' => true]);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => $cantidadLote],
            Carbon::today(),
        )->first();

        $galpon->refresh();

        return [$encargado, $galpon, $lote];
    }
}
