<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneraDescargaReporte;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmpresaContextService;
use App\Services\ReporteHistoriaLoteExcelExporter;
use App\Support\ReporteFiltroLote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DescargarReporteHistoriaLoteController extends Controller
{
    use GeneraDescargaReporte;

    public function __invoke(
        Request $request,
        EmpresaContextService $empresaContext,
        ReporteHistoriaLoteExcelExporter $exporter,
    ): Response {
        Gate::authorize('admin.viewResumen');

        /** @var User $user */
        $user = $request->user();

        $loteId = $request->filled('lote') ? (int) $request->query('lote') : null;

        $filtro = ReporteFiltroLote::desdeParametrosHttp(
            $user,
            $loteId,
            $request->query('desde'),
            $request->query('hasta'),
            $empresaContext->empresaIdFor($user),
        );

        return $this->descargarExcel(
            fn (): string => $exporter->generar($filtro),
            $exporter->nombreArchivo($filtro),
        );
    }
}
