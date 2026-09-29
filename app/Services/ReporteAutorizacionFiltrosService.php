<?php

namespace App\Services;

use App\Exceptions\ReporteConsultaNoDisponibleException;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * REP-08: filtros HTTP (granja/galpón/lote) deben pertenecer al alcance del actor — no export silencioso ajeno.
 */
class ReporteAutorizacionFiltrosService
{
    public function __construct(private EmpresaScopeService $empresaScope) {}

    public function validarGranjaGalpon(User $user, ?int $granjaId, ?int $galponId): void
    {
        if ($galponId !== null) {
            $galpon = $this->buscarGalpon($user, $galponId);

            if ($granjaId !== null && (int) $galpon->granja_id !== $granjaId) {
                throw new ReporteConsultaNoDisponibleException(
                    'El galpón no corresponde a la granja indicada.',
                );
            }
        }

        if ($granjaId !== null) {
            $this->buscarGranja($user, $granjaId);
        }
    }

    public function validarLote(User $user, int $loteId): void
    {
        $this->buscarLote($user, $loteId);
    }

    private function buscarGalpon(User $user, int $galponId): Galpon
    {
        try {
            $galpon = $this->empresaScope->findForActor(Galpon::query(), $user, $galponId);
        } catch (ModelNotFoundException) {
            throw new ReporteConsultaNoDisponibleException(
                'Galpón no disponible en su empresa.',
            );
        }

        assert($galpon instanceof Galpon);

        return $galpon;
    }

    private function buscarGranja(User $user, int $granjaId): Granja
    {
        try {
            $granja = $this->empresaScope->findForActor(Granja::query(), $user, $granjaId);
        } catch (ModelNotFoundException) {
            throw new ReporteConsultaNoDisponibleException(
                'Granja no disponible en su empresa.',
            );
        }

        assert($granja instanceof Granja);

        return $granja;
    }

    private function buscarLote(User $user, int $loteId): Lote
    {
        try {
            $lote = $this->empresaScope->findForActor(Lote::query(), $user, $loteId);
        } catch (ModelNotFoundException) {
            throw new ReporteConsultaNoDisponibleException(
                'Lote no disponible en su empresa.',
            );
        }

        assert($lote instanceof Lote);

        return $lote;
    }
}
