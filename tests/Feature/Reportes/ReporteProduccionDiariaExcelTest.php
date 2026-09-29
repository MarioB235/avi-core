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
use App\Services\ReporteProduccionDiariaExcelExporter;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class ReporteProduccionDiariaExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_contiene_numeros_y_coincide_con_consulta_rep03(): void
    {
        [$dueno, $galpon] = $this->duenoConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 320,
                'huevos_descarte' => 5,
            ]);

        $filtro = ReporteFiltroProduccion::diaUnico($dueno, null, null, now());
        $consulta = app(ReporteConsultaService::class)->produccionDiaria($filtro);
        $binario = app(ReporteProduccionDiariaExcelExporter::class)->generar($filtro);

        $this->assertStringStartsWith('PK', $binario);

        $path = tempnam(sys_get_temp_dir(), 'rep_test_').'.xlsx';
        file_put_contents($path, $binario);

        $reader = new Reader;
        $reader->open($path);

        $filas = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $filas[] = array_map(
                    static fn ($cell) => $cell->getValue(),
                    $row->getCells(),
                );
            }
        }
        $reader->close();
        @unlink($path);

        $headerIndex = null;
        foreach ($filas as $i => $fila) {
            if (($fila[0] ?? null) === 'Día') {
                $headerIndex = $i;
                break;
            }
        }

        $this->assertNotNull($headerIndex);

        $dataRow = $filas[$headerIndex + 1];
        $this->assertSame(320, (int) $dataRow[1]);
        $this->assertSame(5, (int) $dataRow[2]);
        $this->assertSame($consulta['totales_periodo']['huevos'], (int) $dataRow[1]);
    }

    public function test_ruta_excel_requiere_resumen_y_responde_rep03(): void
    {
        [$dueno, $galpon] = $this->duenoConGalpon();

        RegistroOperativo::factory()
            ->forGalponAndUser($galpon, $dueno)
            ->create([
                'tipo' => RegistroOperativoTipo::Huevos,
                'huevos' => 50,
            ]);

        $this->actingAs($dueno)
            ->get(route('dueno.reportes.produccion-diaria'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $operario = User::factory()->create([
            'empresa_id' => $dueno->empresa_id,
            'rol' => UserRole::Operario,
            'must_change_password' => false,
        ]);

        $this->actingAs($operario)
            ->get(route('dueno.reportes.produccion-diaria'))
            ->assertRedirect();
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
