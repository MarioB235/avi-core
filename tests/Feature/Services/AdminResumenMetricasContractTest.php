<?php

namespace Tests\Feature\Services;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\AdminResumenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResumenMetricasContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_aves_actuales_usa_saldo_galpon(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create([
            'aves_actuales' => 4_200,
        ]);

        Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 5_000,
            'estado' => LoteEstado::EnProduccion,
            'fecha_ingreso' => now()->subDays(10)->toDateString(),
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $data = app(AdminResumenService::class)->for($dueno);

        $this->assertSame(4_200, $data->avesActuales);
        $this->assertSame(4_200, $data->galponesResumen[0]['resumen']['aves_actuales']);
    }

    public function test_registros_anulados_no_suman_en_huevos_hoy(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 500,
            ]);

        $anulado = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 9_999,
            ]);
        $anulado->update([
            'estado' => RegistroOperativoEstado::Anulado,
            'anulado_at' => now(),
            'anulado_por' => $dueno->id,
        ]);

        $data = app(AdminResumenService::class)->for($dueno);

        $this->assertSame(500, $data->huevosHoy);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function duenoConGalponYLote(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 3_000,
            'estado' => LoteEstado::EnProduccion,
            'fecha_ingreso' => now()->subDays(12)->toDateString(),
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$dueno, $galpon];
    }
}
