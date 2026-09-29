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
use App\Services\AdminHistorialOperativoService;
use App\Services\AdminResumenService;
use App\Services\ReporteConsultaService;
use App\Services\TotalesCapturaDiaService;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class ReporteConsultaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_produccion_diaria_coincide_con_resumen_e_historial_rep02(): void
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
            ->forGalponAndUser($galponB, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Alimento,
                'alimento_kg' => 40,
            ]);

        $filtro = ReporteFiltroProduccion::diaUnico($dueno, null, null, now());
        $consulta = app(ReporteConsultaService::class)->produccionDiaria($filtro);

        $this->assertFalse($consulta['vacio']);
        $this->assertSame(TotalesCapturaDiaService::class, $consulta['fuente_agregados']);

        $canon = app(TotalesCapturaDiaService::class)->paraUsuario($dueno);
        $this->assertSame($canon, $consulta['filas_dia'][0]['totales']);
        $this->assertSame($canon, $consulta['totales_periodo']);
        $this->assertSame($canon, app(ReporteConsultaService::class)->totalesVistaHoy($dueno));

        $resumen = app(AdminResumenService::class)->for($dueno);
        $this->assertSame($canon['huevos'], $resumen->huevosHoy);
        $this->assertSame($canon['alimento_kg'], $resumen->alimentoKgHoy);

        $historial = app(AdminHistorialOperativoService::class)->totalesCapturaDiaActiva($dueno);
        $this->assertSame($canon, $historial);
    }

    public function test_detalle_por_galpon_suma_al_total_del_dia_rep02(): void
    {
        [$dueno, $galponA, $galponB] = $this->duenoConDosGalpones();

        RegistroOperativo::factory()
            ->forGalponAndUser($galponA, $dueno)
            ->create(['tipo' => RegistroOperativoTipo::Huevos, 'huevos' => 100]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galponB, $dueno)
            ->create(['tipo' => RegistroOperativoTipo::Huevos, 'huevos' => 50]);

        $fecha = now();
        $filas = app(ReporteConsultaService::class)->produccionPorGalponEnDia($dueno, null, null, $fecha);

        $this->assertCount(2, $filas);

        $sumaHuevos = array_sum(array_column(array_column($filas, 'totales'), 'huevos'));
        $totalDia = app(TotalesCapturaDiaService::class)->paraUsuario($dueno, null, null, $fecha);

        $this->assertSame(150, $sumaHuevos);
        $this->assertSame(150, $totalDia['huevos']);
    }

    public function test_periodo_suma_dias_individuales_rep02(): void
    {
        [$dueno, $galpon] = $this->duenoConGalponYLote();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 10,
                'created_at' => now()->subDay(),
            ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 20,
            ]);

        $desde = now()->subDay()->startOfDay();
        $hasta = now()->startOfDay();
        $filtro = new ReporteFiltroProduccion($dueno, null, null, $desde, $hasta);
        $consulta = app(ReporteConsultaService::class)->produccionDiaria($filtro);

        $this->assertCount(2, $consulta['filas_dia']);
        $this->assertSame(30, $consulta['totales_periodo']['huevos']);
    }

    public function test_filtro_rechaza_periodo_invalido(): void
    {
        [$dueno] = $this->duenoConGalponYLote();

        $this->expectException(InvalidArgumentException::class);

        new ReporteFiltroProduccion(
            $dueno,
            null,
            null,
            Carbon::parse('2026-09-10'),
            Carbon::parse('2026-09-01'),
        );
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
}
