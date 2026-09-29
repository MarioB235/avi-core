<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneraDescargaReporte;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmpresaContextService;
use App\Services\ReporteSanidadBasicaExcelExporter;
use App\Support\ReporteFiltroProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DescargarReporteSanidadBasicaController extends Controller
{
    use GeneraDescargaReporte;

    public function __invoke(
        Request $request,
        EmpresaContextService $empresaContext,
        ReporteSanidadBasicaExcelExporter $exporter,
    ): Response {
        Gate::authorize('admin.viewResumen');

        /** @var User $user */
        $user = $request->user();

        $granjaId = $request->filled('granja') ? (int) $request->query('granja') : null;
        $galponId = $request->filled('galpon') ? (int) $request->query('galpon') : null;
        $loteId = $request->filled('lote') ? (int) $request->query('lote') : null;

        $filtro = ReporteFiltroProduccion::desdeParametrosHttp(
            $user,
            $request->query('desde'),
            $request->query('hasta'),
            $granjaId,
            $galponId,
            $empresaContext->empresaIdFor($user),
        );

        return $this->descargarExcel(
            fn (): string => $exporter->generar($filtro, $loteId),
            $exporter->nombreArchivo($filtro),
        );
    }
}
