<?php

namespace Tests\Feature\Reportes;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Exceptions\ReporteConsultaNoDisponibleException;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\ReporteConsultaService;
use App\Services\ReporteHistoriaLoteExcelExporter;
use App\Services\ReporteProduccionDiariaPdfExporter;
use App\Support\ReporteEstadoConsulta;
use App\Support\ReporteFiltroLote;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReporteVaciosExtremosTest extends TestCase
{
    use RefreshDatabase;

    public function test_lote_invalido_no_genera_excel_exitoso_rep07(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $this->actingAs($dueno)
            ->get(route('dueno.reportes.historia-lote', ['lote' => 999_999]))
            ->assertStatus(422);

        $filtro = ReporteFiltroLote::desdeParametrosHttp(
            $dueno,
            999_999,
            now()->toDateString(),
            now()->toDateString(),
            $empresa->id,
        );

        $consulta = app(ReporteConsultaService::class)->historiaLote($filtro);
        $this->assertSame(ReporteEstadoConsulta::NoDisponible->value, $consulta['estado_consulta']);

        $this->expectException(ReporteConsultaNoDisponibleException::class);
        app(ReporteHistoriaLoteExcelExporter::class)->generar($filtro);
    }

    public function test_galpon_inexistente_marca_no_disponible_rep07_rep08(): void
    {
        [$dueno] = $this->duenoConGalpon();

        $filtro = ReporteFiltroProduccion::diaUnico($dueno, null, 999_999, now());
        $consulta = app(ReporteConsultaService::class)->produccionDiaria($filtro);

        $this->assertSame(ReporteEstadoConsulta::NoDisponible->value, $consulta['estado_consulta']);

        $this->actingAs($dueno)
            ->get(route('dueno.reportes.produccion-diaria', ['galpon' => 999_999]))
            ->assertStatus(422);
    }

    public function test_periodo_maximo_y_decimales_en_export_rep07(): void
    {
        [$dueno, $galpon] = $this->duenoConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Alimento,
                'alimento_kg' => 12.35,
            ]);

        $hasta = Carbon::today();
        $desde = $hasta->copy()->subDays(ReporteFiltroProduccion::MAX_DIAS_PERIODO - 1);

        $filtro = new ReporteFiltroProduccion($dueno, null, null, $desde, $hasta);
        $consulta = app(ReporteConsultaService::class)->produccionDiaria($filtro);

        $this->assertSame(ReporteEstadoConsulta::Ok->value, $consulta['estado_consulta']);
        $this->assertCount(ReporteFiltroProduccion::MAX_DIAS_PERIODO, $consulta['filas_dia']);
        $this->assertSame(12.35, $consulta['totales_periodo']['alimento_kg']);

        $pdf = app(ReporteProduccionDiariaPdfExporter::class)->generar($filtro);
        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(4000, strlen($pdf));
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function duenoConGalpon(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->conLoteActivo([
            'estado' => LoteEstado::EnProduccion,
        ])->create();

        return [$dueno, $galpon];
    }
}
