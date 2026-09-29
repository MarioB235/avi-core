<?php

namespace Tests\Feature\Reportes;

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
use App\Services\ReporteConsultaService;
use App\Services\ReporteProduccionDiariaPdfExporter;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteProduccionDiariaPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_valido_y_totales_coinciden_con_consulta_rep04(): void
    {
        [$dueno, $galpon] = $this->duenoConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 275,
            ]);

        $filtro = ReporteFiltroProduccion::diaUnico($dueno, null, null, now());
        $consulta = app(ReporteConsultaService::class)->produccionDiaria($filtro);
        $pdf = app(ReporteProduccionDiariaPdfExporter::class)->generar($filtro);

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertGreaterThan(800, strlen($pdf));
        $this->assertSame(275, $consulta['totales_periodo']['huevos']);
    }

    public function test_ruta_pdf_responde_con_content_type_rep04(): void
    {
        [$dueno, $galpon] = $this->duenoConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 10,
            ]);

        $this->actingAs($dueno)
            ->get(route('dueno.reportes.produccion-diaria-pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function duenoConGalpon(): array
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();

        Lote::factory()->forGalpon($galpon)->create([
            'cantidad_inicial' => 2_000,
            'estado' => LoteEstado::EnProduccion,
            'fecha_ingreso' => now()->subDays(5)->toDateString(),
        ]);

        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        return [$dueno, $galpon];
    }
}
