<?php

namespace App\Http\View\Composers;

use App\Models\Galpon;
use App\Services\OperarioGalponService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\View\View;

class OperarioLayoutComposer
{
    public function __construct(
        private OperarioGalponService $operarioGalponService,
    ) {}

    public function compose(View $view): void
    {
        $user = Auth::user();
        $galpon = $user ? $this->operarioGalponService->galponActual($user) : null;

        $view->with([
            'operarioHeaderTitle' => $this->headerTitle(),
            'operarioHeaderSubtitle' => $this->headerSubtitle($galpon),
        ]);
    }

    private function headerTitle(): string
    {
        if (Request::routeIs('operario.home')) {
            return 'Inicio';
        }

        if (Request::routeIs('operario.galpon')) {
            return 'Galpón';
        }

        if (Request::routeIs('operario.cargar', 'operario.carga.*')) {
            return 'Cargar';
        }

        if (Request::routeIs('operario.historial')) {
            return 'Historial';
        }

        return 'Operario';
    }

    private function headerSubtitle(?Galpon $galpon): ?string
    {
        if (Request::routeIs('operario.galpon')) {
            return 'Seleccioná dónde vas a cargar hoy';
        }

        if ($galpon === null) {
            return 'Elegí un galpón en la pestaña Galpón';
        }

        return $this->operarioGalponService->etiquetaGalpon($galpon);
    }
}
