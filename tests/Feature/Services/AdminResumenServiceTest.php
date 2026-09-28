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
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminResumenServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

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

    public function test_for_filters_by_galpon_id(): void
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

        $data = app(AdminResumenService::class)->for($dueno, null, $galponB->id);

        $this->assertSame(200, $data->huevosHoy);
        $this->assertSame(1, $data->galponesActivos);
    }

    public function test_postura_semanal_sums_huevos_by_day_for_scope(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 300,
                'created_at' => now()->subDays(2),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 450,
                'created_at' => now(),
            ]);

        $puntos = app(AdminResumenService::class)->posturaSemanal($dueno);

        $this->assertCount(7, $puntos);
        $this->assertSame(750, collect($puntos)->sum('value'));
    }

    public function test_postura_semanal_uses_logical_day_in_empresa_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-28 03:30:00', 'UTC'));

        [$dueno, $galpon] = $this->duenoConGalponYLote();

        $dueno->empresa?->forceFill([
            'configuracion' => [
                'zona_horaria' => 'America/Montevideo',
                'unidades' => [
                    'huevos_por_maple' => 30,
                    'maples_por_cajon' => 12,
                ],
            ],
        ])->save();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 111,
                'created_at' => Carbon::parse('2026-09-28 02:30:00', 'UTC'),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 222,
                'created_at' => Carbon::parse('2026-09-28 04:00:00', 'UTC'),
            ]);

        $puntos = app(AdminResumenService::class)->posturaSemanal($dueno);
        $hoy = collect($puntos)->firstWhere('date', '2026-09-28');

        $this->assertNotNull($hoy);
        $this->assertSame(222, $hoy['value']);
        $this->assertSame(111, collect($puntos)->firstWhere('date', '2026-09-27')['value'] ?? 0);
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

    public function test_for_excludes_galpones_in_inactive_granja(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();
        $galpon->granja->update(['activa' => false]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 500,
            ]);

        $data = app(AdminResumenService::class)->for($dueno);

        $this->assertSame(0, $data->huevosHoy);
        $this->assertSame(0, $data->galponesActivos);
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

        $data = app(AdminResumenService::class)->for($dueno, null, $galponAjeno->id);

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

    public function test_for_includes_descarte_and_alimento_totals(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
                'huevos_descarte' => 25,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Alimento,
                'alimento_kg' => 150.5,
            ]);

        $data = app(AdminResumenService::class)->for($dueno);

        $this->assertSame(400, $data->huevosHoy);
        $this->assertSame(25, $data->huevosDescarteHoy);
        $this->assertSame(150.5, $data->alimentoKgHoy);
        $this->assertSame(150.5, $data->galponesResumen[0]['alimento_kg_hoy']);
    }

    public function test_pulso_for_lists_galpones_without_carga_today(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 300,
            ]);

        $pulso = app(AdminResumenService::class)->pulsoFor($dueno);

        $this->assertSame('atencion', $pulso['estado']);
        $this->assertCount(1, $pulso['galpones_sin_carga']);
        $this->assertSame($galponB->id, $pulso['galpones_sin_carga'][0]['id']);
        $this->assertSame(300, $pulso['huevos_hoy']);
    }

    public function test_pulso_for_compares_huevos_with_yesterday(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
                'created_at' => now()->subDay(),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 250,
            ]);

        $pulso = app(AdminResumenService::class)->pulsoFor($dueno);

        $this->assertSame(250, $pulso['huevos_hoy']);
        $this->assertSame(200, $pulso['huevos_ayer']);
        $this->assertSame(50, $pulso['delta_huevos']);
        $this->assertSame(25.0, $pulso['delta_huevos_pct']);
    }

    public function test_pulso_for_marks_revision_when_mortality_alert(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote(cantidadInicial: 1000);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'muertes' => 15,
                'created_at' => now()->subDays(2),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 100,
            ]);

        $pulso = app(AdminResumenService::class)->pulsoFor($dueno);

        $this->assertSame('revision', $pulso['estado']);
        $this->assertCount(1, $pulso['alertas']);
        $this->assertSame($galpon->id, $pulso['alertas'][0]['galpon_id']);
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
