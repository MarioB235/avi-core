<?php

namespace Tests\Feature\Reportes;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use App\Services\ReporteConsultaService;
use App\Support\ReporteEstadoConsulta;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteAutorizacionDescargaTest extends TestCase
{
    use RefreshDatabase;

    public function test_galpon_de_otra_empresa_responde_422_rep08(): void
    {
        [$duenoA, $galponB] = $this->dosEmpresasConGalpon();

        $this->actingAs($duenoA)
            ->get(route('dueno.reportes.produccion-diaria', ['galpon' => $galponB->id]))
            ->assertStatus(422);
    }

    public function test_lote_ajeno_en_historia_responde_422_rep08(): void
    {
        [$duenoA, , $loteB] = $this->dosEmpresasConLote();

        $this->actingAs($duenoA)
            ->get(route('dueno.reportes.historia-lote', ['lote' => $loteB->id]))
            ->assertStatus(422);
    }

    public function test_operario_no_descarga_reporte_admin_rep08(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $operario = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('dueno.reportes.produccion-diaria'))
            ->assertRedirect();
    }

    public function test_consulta_marca_no_disponible_si_granja_no_coincide_rep08(): void
    {
        $empresa = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);
        $granjaA = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $granjaB = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granjaA)->conLoteActivo([
            'estado' => LoteEstado::EnProduccion,
        ])->create();

        $filtro = ReporteFiltroProduccion::diaUnico($dueno, $granjaB->id, $galpon->id, now());
        $consulta = app(ReporteConsultaService::class)->produccionDiaria($filtro);

        $this->assertSame(ReporteEstadoConsulta::NoDisponible->value, $consulta['estado_consulta']);
    }

    /**
     * @return array{0: User, 1: Galpon}
     */
    private function dosEmpresasConGalpon(): array
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $duenoA = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $granjaB = Granja::factory()->create(['empresa_id' => $empresaB->id]);
        $galponB = Galpon::factory()->forGranja($granjaB)->conLoteActivo([
            'estado' => LoteEstado::EnProduccion,
        ])->create();

        return [$duenoA, $galponB];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
     */
    private function dosEmpresasConLote(): array
    {
        $empresaA = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);
        $empresaB = Empresa::factory()->create(['estado' => EmpresaEstado::Activa]);

        $duenoA = User::factory()->create([
            'empresa_id' => $empresaA->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);

        $granjaB = Granja::factory()->create(['empresa_id' => $empresaB->id]);
        $galponB = Galpon::factory()->forGranja($granjaB)->conLoteActivo([
            'estado' => LoteEstado::EnProduccion,
        ])->create();

        $loteB = $galponB->lotes()->first();
        $this->assertNotNull($loteB);

        return [$duenoA, $galponB, $loteB];
    }
}
