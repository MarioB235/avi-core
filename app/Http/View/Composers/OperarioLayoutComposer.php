<?php

namespace App\Http\View\Composers;

use App\Models\Galpon;
use App\Services\OperarioGalponService;
use App\Support\OperarioNav;
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
            'operarioHeaderTitle' => Request::routeIs('operario.perfil')
                ? $this->perfilHeaderTitle()
                : OperarioNav::headerTitle(),
            'operarioHeaderSubtitle' => $this->headerSubtitle($galpon),
            'operarioHasGalpon' => $galpon !== null,
            'operarioIsHeroPage' => Request::routeIs('operario.home', 'operario.cargar', 'operario.historial', 'operario.perfil'),
        ]);
    }

    private function headerSubtitle(?Galpon $galpon): ?string
    {
        if ($galpon === null) {
            return 'Elegí un galpón en Inicio';
        }

        return $this->operarioGalponService->etiquetaGalpon($galpon);
    }

    private function perfilHeaderTitle(): string
    {
        return Request::query('seccion') === 'password'
            ? 'Contraseña'
            : 'Mis datos';
    }
}
