<?php

namespace Tests\Feature\Services;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
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

class AdminResumenServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_aggregates_kpis_for_company_galpones(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 250,
            ]);

        $data = app(AdminResumenService::class)->for($dueno);

        $this->assertSame(650, $data->huevosHoy);
        $this->assertSame(2, $data->galponesActivos);
        $this->assertCount(2, $data->galponesResumen);
    }

    public function test_for_filters_by_granja_id(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
            ]);

        $data = app(AdminResumenService::class)->for($dueno, $galponA->granja_id);

        $this->assertSame(400, $data->huevosHoy);
        $this->assertSame(1, $data->galponesActivos);
    }

    public function test_for_filters_by_galpon_ids(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
            ]);

        $data = app(AdminResumenService::class)->for($dueno, null, [$galponB->id]);

        $this->assertSame(200, $data->huevosHoy);
        $this->assertSame(1, $data->galponesActivos);
    }

    public function test_for_flags_mortality_alert_above_reference(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote(cantidadInicial: 1000);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 15,
                'created_at' => now()->subDays(3),
            ]);

        $data = app(AdminResumenService::class)->for($dueno);

        $this->assertSame(1, $data->alertasCount);
        $this->assertTrue($data->galponesResumen[0]['alerta_mortalidad']);
        $this->assertGreaterThan(AdminResumenService::MORTALIDAD_REFERENCIA_PCT, $data->galponesResumen[0]['mortalidad_pct']);
    }

    public function test_for_excludes_other_company_galpones(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        $otraEmpresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaAjena = Granja::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $galponAjeno = Galpon::factory()->forGranja($granjaAjena)->create();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 500,
            ]);

        $data = app(AdminResumenService::class)->for($dueno, null, [$galponAjeno->id]);

        $this->assertSame(0, $data->huevosHoy);
        $this->assertSame(0, $data->galponesActivos);
    }

    public function test_teaser_for_dueno_returns_operativo_counts(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 320,
            ]);

        $teaser = app(AdminResumenService::class)->teaserFor($dueno);

        $this->assertSame(320, $teaser['huevos_hoy']);
        $this->assertSame(1, $teaser['galpones_activos']);
    }

    public function test_teaser_returns_zeros_for_user_without_resumen_access(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $teaser = app(AdminResumenService::class)->teaserFor($operario);

        $this->assertSame([
            'huevos_hoy' => 0,
            'muertes_hoy' => 0,
            'alertas_count' => 0,
            'galpones_activos' => 0,
        ], $teaser);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function duenoConGalponYLote(int $cantidadInicial = 5000): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        Lote::factory()
            ->forGalpon($galpon)
            ->create([
                'cantidad_inicial' => $cantidadInicial,
                'estado' => LoteEstado::EnProduccion,
                'fecha_ingreso' => now()->subDays(20)->toDateString(),
            ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$dueno, $galpon];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Galpon}
     */
    private function duenoConDosGalpones(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Granja A']);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id, 'nombre' => 'Granja B']);
        $galponA = Galpon::factory()->forGranja($granjaA)->create(['nombre' => 'Galpón A']);
        $galponB = Galpon::factory()->forGranja($granjaB)->create(['nombre' => 'Galpón B']);

        foreach ([$galponA, $galponB] as $galpon) {
            Lote::factory()
                ->forGalpon($galpon)
                ->create([
                    'cantidad_inicial' => 3000,
                    'estado' => LoteEstado::EnProduccion,
                    'fecha_ingreso' => now()->subDays(15)->toDateString(),
                ]);
        }

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$dueno, $galponA, $galponB];
    }
}
