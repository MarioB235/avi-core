<?php

namespace Tests\Feature\Services;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Movimiento\RegistrarCierreLoteAction;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\TipoHuevo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\AdminResumenService;
use App\Support\MortalidadVentanaGalpon;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResumenMortalidadVentanaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cierre_lote_no_resetea_mortalidad_ni_denominador(): void
    {
        [$encargado, $galpon] = $this->encargadoConGalponVacio();

        $fechaIngreso = Carbon::today()->subDays(30);

        $lote = app(RegistrarLoteAction::class)->execute(
            $encargado,
            $galpon,
            [TipoHuevo::Blanco->value => 1_000],
            $fechaIngreso->copy()->subWeeks(20),
            $fechaIngreso,
        )->first();

        $galpon->refresh();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $encargado)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'muertes' => 15,
                'created_at' => now()->subDays(2),
            ]);

        $galpon->decrement('aves_actuales', 15);
        $galpon->refresh();

        $antes = app(AdminResumenService::class)->for($encargado);
        $this->assertSame(1, $antes->alertasCount);
        $this->assertSame(1.5, $antes->galponesResumen[0]['mortalidad_pct']);

        app(RegistrarCierreLoteAction::class)->execute(
            $encargado,
            $galpon,
            $lote,
            985,
            'Cierre de ciclo',
            'Faena',
        );

        $lote->refresh();
        $this->assertSame(LoteEstado::Cerrado, $lote->estado);

        $despues = app(AdminResumenService::class)->for($encargado);
        $fila = $despues->galponesResumen[0];

        $this->assertSame(1.5, $fila['mortalidad_pct']);
        $this->assertTrue($fila['mortalidad_incluye_cerrados']);
        $this->assertSame(1, $despues->alertasCount);

        $metrica = app(MortalidadVentanaGalpon::class)->metricaParaGalpon($galpon);
        $this->assertSame(15, $metrica['muertes_acumuladas']);
        $this->assertSame(1_000, $metrica['poblacion_inicial']);
    }

    public function test_varios_lotes_activos_marca_solo_galpon_sin_tasa_por_lote(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 5_000]);

        Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 3_000,
            'estado' => LoteEstado::EnProduccion,
            'fecha_ingreso' => now()->subDays(30)->toDateString(),
        ]);

        Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 2_000,
            'estado' => LoteEstado::EnProduccion,
            'fecha_ingreso' => now()->subDays(10)->toDateString(),
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'muertes' => 50,
                'created_at' => now()->subDays(5),
            ]);

        $data = app(AdminResumenService::class)->for($dueno);
        $fila = $data->galponesResumen[0];

        $this->assertTrue($fila['mortalidad_solo_galpon']);
        $this->assertFalse($fila['mortalidad_incluye_cerrados']);
        $this->assertSame(1.0, $fila['mortalidad_pct']);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function encargadoConGalponVacio(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => 0,
            'activo' => true,
        ]);

        return [$encargado, $galpon];
    }
}
