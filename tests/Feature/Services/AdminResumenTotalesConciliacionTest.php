<?php

namespace Tests\Feature\Services;

use App\Actions\Auditoria\CorregirRegistroOperativoAction;
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
use App\Services\AdminHistorialOperativoService;
use App\Services\AdminHomeService;
use App\Services\AdminResumenService;
use App\Services\TotalesCapturaDiaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminResumenTotalesConciliacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_inicio_resumen_historial_coinciden_en_fixture_conocido(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 300,
                'huevos_descarte' => 12,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'muertes' => 4,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 150,
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Alimento,
                'alimento_kg' => 42.5,
            ]);

        $esperado = [
            'huevos' => 450,
            'huevos_descarte' => 12,
            'muertes' => 4,
            'descarte_aves' => 0,
            'alimento_kg' => 42.5,
        ];

        $this->assertTotalesConciliados($dueno, $esperado);
    }

    public function test_anulados_excluidos_de_totales_conciliados(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 200,
            ]);

        $anulado = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 9_000,
            ]);

        $anulado->update([
            'estado' => RegistroOperativoEstado::Anulado,
            'anulado_at' => now(),
            'anulado_por' => $dueno->id,
        ]);

        $this->assertTotalesConciliados($dueno, [
            'huevos' => 200,
            'huevos_descarte' => 0,
            'muertes' => 0,
            'descarte_aves' => 0,
            'alimento_kg' => 0.0,
        ]);
    }

    public function test_correccion_supervisor_reflejada_igual_en_tres_fuentes(): void
    {
        [$encargado, $operario, $galpon] = $this->encargadoConGalpon();

        $registro = RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $operario)
            ->create([
                'tipo' => RegistroOperativoTipo::Muertes,
                'muertes' => 8,
            ]);

        $galpon->decrement('aves_actuales', 8);

        app(CorregirRegistroOperativoAction::class)->execute(
            $encargado,
            $registro,
            'Ajuste de conteo',
            ['muertes' => 3],
        );

        $this->assertTotalesConciliados($encargado, [
            'huevos' => 0,
            'huevos_descarte' => 0,
            'muertes' => 3,
            'descarte_aves' => 0,
            'alimento_kg' => 0.0,
        ]);
    }

    public function test_filtro_granja_concilia_resumen_e_historial(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galponA = Galpon::factory()->forGranja($granjaA)->create();
        $galponB = Galpon::factory()->forGranja($granjaB)->create();

        foreach ([$galponA, $galponB] as $galpon) {
            Lote::factory()->forGalpon($galpon)->create([
                'cantidad_inicial' => 2_000,
                'estado' => LoteEstado::EnProduccion,
                'fecha_ingreso' => now()->subDays(8)->toDateString(),
            ]);
        }

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create(['tipo' => RegistroOperativoTipo::Huevos, 'huevos' => 100]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create(['tipo' => RegistroOperativoTipo::Huevos, 'huevos' => 500]);

        $esperado = [
            'huevos' => 100,
            'huevos_descarte' => 0,
            'muertes' => 0,
            'descarte_aves' => 0,
            'alimento_kg' => 0.0,
        ];

        $resumen = app(AdminResumenService::class)->for($dueno, $granjaA->id);
        $historial = app(AdminHistorialOperativoService::class)->totalesCapturaDiaActiva($dueno, $granjaA->id);
        $canon = app(TotalesCapturaDiaService::class)->paraUsuario($dueno, $granjaA->id);

        $this->assertSame($esperado, $canon);
        $this->assertSame($esperado['huevos'], $resumen->huevosHoy);
        $this->assertSame($esperado, $historial);
    }

    /**
     * @param  array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float}  $esperado
     */
    private function assertTotalesConciliados(User $actor, array $esperado): void
    {
        $canon = app(TotalesCapturaDiaService::class)->paraUsuario($actor);
        $this->assertSame($esperado, $canon);

        $resumen = app(AdminResumenService::class)->for($actor);
        $this->assertSame($esperado['huevos'], $resumen->huevosHoy);
        $this->assertSame($esperado['huevos_descarte'], $resumen->huevosDescarteHoy);
        $this->assertSame($esperado['muertes'], $resumen->muertesHoy);
        $this->assertSame($esperado['alimento_kg'], $resumen->alimentoKgHoy);

        $pulso = app(AdminResumenService::class)->pulsoFor($actor);
        $this->assertSame($esperado['huevos'], $pulso['huevos_hoy']);
        $this->assertSame($esperado['muertes'], $pulso['muertes_hoy']);

        $teaser = app(AdminResumenService::class)->teaserFor($actor);
        $this->assertSame($esperado['huevos'], $teaser['huevos_hoy']);
        $this->assertSame($esperado['muertes'], $teaser['muertes_hoy']);

        $home = app(AdminHomeService::class)->for($actor);
        $this->assertSame($esperado['huevos'], $home->operativoTeaser['huevos_hoy']);
        $this->assertSame($esperado['huevos'], $home->pulso['huevos_hoy']);

        $historial = app(AdminHistorialOperativoService::class)->totalesCapturaDiaActiva($actor);
        $this->assertSame($esperado, $historial);
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
            'fecha_ingreso' => now()->subDays(10)->toDateString(),
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
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galponA = Galpon::factory()->forGranja($granja)->create();
        $galponB = Galpon::factory()->forGranja($granja)->create();

        foreach ([$galponA, $galponB] as $galpon) {
            Lote::factory()->forGalpon($galpon)->create([
                'cantidad_inicial' => 4_000,
                'estado' => LoteEstado::EnProduccion,
                'fecha_ingreso' => now()->subDays(14)->toDateString(),
            ]);
        }

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$dueno, $galponA, $galponB];
    }

    /**
     * @return array{0: User, 1: User, 2: Galpon}
     */
    private function encargadoConGalpon(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create(['aves_actuales' => 1_000]);

        Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 1_000,
            'estado' => LoteEstado::EnProduccion,
            'fecha_ingreso' => now()->subDays(7)->toDateString(),
        ]);

        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
            'must_change_password' => false,
        ]);

        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        return [$encargado, $operario, $galpon];
    }
}
