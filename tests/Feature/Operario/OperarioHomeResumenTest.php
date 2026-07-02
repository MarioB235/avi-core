<?php

namespace Tests\Feature\Operario;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Livewire\Operario\Home;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\OperarioGalponResumenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperarioHomeResumenTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_without_galpon_shows_selector_prompt(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => null,
        ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('Seleccioná un galpón para ver el estado.', false)
            ->assertDontSee('Aves actuales', false)
            ->assertDontSee('Últimas cargas', false);
    }

    public function test_home_shows_galpon_summary_and_lotes(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote([
            'aves_actuales' => 10500,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 900,
            ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('10.500', false)
            ->assertSee('Aves actuales', false)
            ->assertSee('900', false)
            ->assertSee('Huevos hoy', false)
            ->assertSee($lote->codigo, false)
            ->assertSee('inicio', false)
            ->assertDontSee('Ver historial', false)
            ->assertDontSee('Últimas cargas', false);
    }

    public function test_home_huevos_today_are_galpon_scoped_not_user_scoped(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();

        $otroOperario = User::factory()->create([
            'empresa_id' => $operario->empresa_id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => $galpon->id,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $otroOperario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 600,
            ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('600', false)
            ->assertSee('Huevos hoy', false);
    }

    public function test_resumen_service_accumulated_huevos_respect_lote_ingreso_window(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote(loteOverrides: [
            'fecha_ingreso' => now()->subDays(10)->toDateString(),
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
                'created_at' => now()->subDays(20),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 300,
                'created_at' => now()->subDays(5),
            ]);

        $resumen = app(OperarioGalponResumenService::class)->resumen($galpon);

        $this->assertSame(300, $resumen['huevos_acumulados']);
        $this->assertSame($lote->fecha_ingreso->toDateString(), $resumen['fecha_inicio_ventana']->toDateString());
    }

    public function test_home_shows_multiple_lotes_notice(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();

        Lote::factory()
            ->forGalpon($galpon)
            ->create([
                'codigo' => 'L-2026-02',
                'fecha_ingreso' => now()->subMonths(2)->toDateString(),
                'estado' => LoteEstado::Activo,
            ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('más de un lote activo', false)
            ->assertSee('L-2026-02', false);
    }

    public function test_home_shows_muertes_kpi_and_acumuladas_footnote(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote(loteOverrides: [
            'fecha_ingreso' => now()->subDays(10)->toDateString(),
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 2,
                'created_at' => now(),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'huevos' => null,
                'muertes' => 5,
                'created_at' => now()->subDays(5),
            ]);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee('Muertes hoy', false)
            ->assertSee('muertes acumuladas', false)
            ->assertSee('7 muertes acumuladas', false);
    }

    public function test_resumen_service_maples_from_huevos(): void
    {
        [$operario, $galpon] = $this->createOperarioConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 1500,
            ]);

        $resumen = app(OperarioGalponResumenService::class)->resumen($galpon);

        $this->assertSame(1500, $resumen['huevos_hoy']);
        $this->assertSame(50, $resumen['maples_hoy']);
        $this->assertSame(50, $resumen['maples_acumulados']);
    }

    public function test_resumen_service_without_active_lotes_returns_zero_accumulated(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote();

        $lote->update(['estado' => LoteEstado::Cerrado]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 400,
            ]);

        $resumen = app(OperarioGalponResumenService::class)->resumen($galpon->fresh());

        $this->assertTrue($resumen['lotes']->isEmpty());
        $this->assertNull($resumen['fecha_inicio_ventana']);
        $this->assertSame(400, $resumen['huevos_hoy']);
        $this->assertSame(0, $resumen['huevos_acumulados']);
        $this->assertSame(0, $resumen['muertes_acumuladas']);
    }

    public function test_home_shows_lote_age_from_resumen_service(): void
    {
        [$operario, $galpon, $lote] = $this->createOperarioConGalponYLote(loteOverrides: [
            'fecha_nacimiento' => now()->subWeeks(18)->toDateString(),
        ]);

        $edadEsperada = app(OperarioGalponResumenService::class)->edadSemanas($lote);

        Livewire::actingAs($operario)
            ->test(Home::class)
            ->assertSee($lote->codigo, false)
            ->assertSee("{$edadEsperada} semanas", false);
    }

    /**
     * @param  array<string, mixed>  $galponOverrides
     * @param  array<string, mixed>  $loteOverrides
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function createOperarioConGalponYLote(
        array $galponOverrides = [],
        array $loteOverrides = [],
    ): array {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create($galponOverrides);

        $lote = Lote::factory()
            ->forGalpon($galpon)
            ->create(array_merge([
                'codigo' => 'L-2026-01',
                'fecha_nacimiento' => now()->subMonths(5)->toDateString(),
                'fecha_ingreso' => now()->subMonths(4)->toDateString(),
                'cantidad_inicial' => 10500,
                'estado' => LoteEstado::EnProduccion,
            ], $loteOverrides));

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
            'ultimo_galpon_id' => $galpon->id,
        ]);

        return [$operario, $galpon, $lote];
    }
}
