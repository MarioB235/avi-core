<?php

namespace Tests\Feature\Reportes;

use App\Actions\Operacion\AnularVacunacionAction;
use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Enums\VacunaTipo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use App\Services\ReporteConsultaService;
use App\Services\ReporteHistoriaLoteExcelExporter;
use App\Support\ReporteFiltroLote;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteHistoriaLoteSanidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_historia_lote_no_atribuye_huevos_con_varios_lotes_activos_rep06(): void
    {
        [$dueno, $galpon, $loteA] = $this->duenoConGalpon();

        Lote::factory()->forGalpon($galpon)->create([
            'codigo' => 'LOTE-B',
            'estado' => LoteEstado::Activo,
        ]);

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 999,
            ]);

        $filtro = ReporteFiltroLote::desdeParametrosHttp(
            $dueno,
            $loteA->id,
            now()->toDateString(),
            now()->toDateString(),
            $dueno->empresa_id,
        );

        $consulta = app(ReporteConsultaService::class)->historiaLote($filtro);

        $this->assertFalse($consulta['produccion_periodo']['atribuible']);
        $this->assertNull($consulta['produccion_periodo']['huevos_aptos']);
        $this->assertStringContainsString('varios lotes', (string) $consulta['produccion_periodo']['aviso']);
    }

    public function test_historia_lote_atribuye_huevos_con_lote_unico_rep06(): void
    {
        [$dueno, $galpon, $lote] = $this->duenoConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 120,
            ]);

        $filtro = ReporteFiltroLote::desdeParametrosHttp(
            $dueno,
            $lote->id,
            now()->toDateString(),
            now()->toDateString(),
            $dueno->empresa_id,
        );

        $consulta = app(ReporteConsultaService::class)->historiaLote($filtro);

        $this->assertTrue($consulta['produccion_periodo']['atribuible']);
        $this->assertSame(120, $consulta['produccion_periodo']['huevos_aptos']);
    }

    public function test_sanidad_lista_vacunacion_y_anulacion_rep06(): void
    {
        [$dueno, $galpon, $lote, $operario] = $this->duenoConGalponYOperario();

        $vacunacion = Vacunacion::factory()->create([
            'empresa_id' => $dueno->empresa_id,
            'galpon_id' => $galpon->id,
            'lote_id' => $lote->id,
            'user_id' => $operario->id,
            'vacuna' => VacunaTipo::Newcastle,
        ]);

        app(AnularVacunacionAction::class)->execute($dueno, $vacunacion, 'Error de carga');

        $filtro = ReporteFiltroProduccion::diaUnico($dueno, null, $galpon->id, now());
        $consulta = app(ReporteConsultaService::class)->sanidadBasica($filtro, $lote->id);

        $this->assertCount(1, $consulta['filas']);
        $this->assertSame('Anulado', $consulta['filas'][0]['estado']);
    }

    public function test_rutas_excel_rep06(): void
    {
        [$dueno, $galpon, $lote] = $this->duenoConGalpon();

        $filtro = ReporteFiltroLote::desdeParametrosHttp(
            $dueno,
            $lote->id,
            now()->toDateString(),
            now()->toDateString(),
            $dueno->empresa_id,
        );
        $bytes = app(ReporteHistoriaLoteExcelExporter::class)->generar($filtro);
        $this->assertStringStartsWith('PK', $bytes);

        $this->actingAs($dueno)
            ->get(route('dueno.reportes.historia-lote', ['lote' => $lote->id]))
            ->assertOk();

        $this->actingAs($dueno)
            ->get(route('dueno.reportes.sanidad-basica'))
            ->assertOk();
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote}
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
            'codigo' => 'LOTE-A',
        ])->create();

        $lote = $galpon->lotes()->first();
        $this->assertNotNull($lote);

        return [$dueno, $galpon, $lote];
    }

    /**
     * @return array{0: User, 1: Galpon, 2: Lote, 3: User}
     */
    private function duenoConGalponYOperario(): array
    {
        [$dueno, $galpon, $lote] = $this->duenoConGalpon();
        $operario = User::factory()->create([
            'empresa_id' => $dueno->empresa_id,
            'rol' => UserRole::Operario,
        ]);

        return [$dueno, $galpon, $lote, $operario];
    }
}
