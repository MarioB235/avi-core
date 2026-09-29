<?php

namespace Tests\Feature\Reportes;

use App\Enums\EmpresaEstado;
use App\Enums\LoteEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\User;
use App\Services\ReporteMovimientosExistenciasPdfExporter;
use App\Services\ReporteProduccionDiariaExcelExporter;
use App\Services\ReporteProduccionDiariaPdfExporter;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class ReporteContenidoSeguroTest extends TestCase
{
    use RefreshDatabase;

    public function test_excel_prefija_celda_empresa_si_parece_formula_rep09(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
            'nombre' => '=CMD("calc")',
        ]);
        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->conLoteActivo([
            'estado' => LoteEstado::EnProduccion,
        ])->create();

        $filtro = ReporteFiltroProduccion::diaUnico($dueno, null, null, now());
        $bytes = app(ReporteProduccionDiariaExcelExporter::class)->generar($filtro);

        $nombreEmpresa = $this->celdaExcel($bytes, 'Empresa', 1);

        $this->assertSame("'=CMD(\"calc\")", $nombreEmpresa);
    }

    public function test_pdf_empresa_con_formula_genera_binario_valido_rep09(): void
    {
        $empresa = Empresa::factory()->create([
            'estado' => EmpresaEstado::Activa,
            'nombre' => '=1+1',
        ]);
        $dueno = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'must_change_password' => false,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        Galpon::factory()->forGranja($granja)->conLoteActivo([
            'estado' => LoteEstado::EnProduccion,
        ])->create();

        $filtro = ReporteFiltroProduccion::diaUnico($dueno, null, null, now());

        $pdfProd = app(ReporteProduccionDiariaPdfExporter::class)->generar($filtro);
        $this->assertStringStartsWith('%PDF', $pdfProd);

        $pdfMov = app(ReporteMovimientosExistenciasPdfExporter::class)->generar($filtro);
        $this->assertStringStartsWith('%PDF', $pdfMov);
    }

    private function celdaExcel(string $binario, string $etiquetaFila, int $indiceColumna): mixed
    {
        $path = tempnam(sys_get_temp_dir(), 'rep_seg_').'.xlsx';
        file_put_contents($path, $binario);

        $reader = new Reader;
        $reader->open($path);

        $valor = null;
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->getCells();
                if (($cells[0]->getValue() ?? null) === $etiquetaFila) {
                    $valor = $cells[$indiceColumna]->getValue();
                    break 2;
                }
            }
        }

        $reader->close();
        @unlink($path);

        return $valor;
    }
}
