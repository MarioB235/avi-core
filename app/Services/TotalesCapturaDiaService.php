<?php

namespace App\Services;

use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Support\DiaOperativoEmpresa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

/**
 * Agregación canónica de capturas del día operativo (RES-04).
 * Misma población que Resumen: galpones disponiblesParaCarga en scope empresa + filtros.
 */
class TotalesCapturaDiaService
{
    public function __construct(
        private EmpresaContextService $empresaContext,
        private EmpresaScopeService $empresaScope,
        private SoporteEmpresaService $soporte,
    ) {}

    /**
     * @return array{
     *     huevos: int,
     *     huevos_descarte: int,
     *     muertes: int,
     *     descarte_aves: int,
     *     alimento_kg: float,
     * }
     */
    public function paraUsuario(User $user, ?int $granjaId = null, ?int $galponId = null, ?Carbon $fechaLogica = null): array
    {
        if (! $this->soporte->canViewResumenOperativo($user)) {
            return $this->vacios();
        }

        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return $this->vacios();
        }

        $galponIds = $this->galponesEnScope($user, $granjaId, $galponId)->modelKeys();

        if ($galponIds === []) {
            return $this->vacios();
        }

        $dia = $fechaLogica !== null
            ? DiaOperativoEmpresa::enFechaParaEmpresa($empresaId, $fechaLogica->toDateString())
            : DiaOperativoEmpresa::hoyParaEmpresa($empresaId);

        $query = RegistroOperativo::query()
            ->activos()
            ->where('empresa_id', $empresaId)
            ->whereIn('galpon_id', $galponIds)
            ->where('created_at', '>=', $dia->inicioUtc())
            ->where('created_at', '<', $dia->finUtc());

        $fila = $query
            ->selectRaw('COALESCE(SUM(huevos), 0) as huevos')
            ->selectRaw('COALESCE(SUM(huevos_descarte), 0) as huevos_descarte')
            ->selectRaw('COALESCE(SUM(muertes), 0) as muertes')
            ->selectRaw('COALESCE(SUM(descarte_aves), 0) as descarte_aves')
            ->selectRaw('COALESCE(SUM(alimento_kg), 0) as alimento_kg')
            ->first();

        return [
            'huevos' => (int) ($fila->huevos ?? 0),
            'huevos_descarte' => (int) ($fila->huevos_descarte ?? 0),
            'muertes' => (int) ($fila->muertes ?? 0),
            'descarte_aves' => (int) ($fila->descarte_aves ?? 0),
            'alimento_kg' => (float) ($fila->alimento_kg ?? 0),
        ];
    }

    /**
     * @return Collection<int, Galpon>
     */
    public function galponesEnScope(User $user, ?int $granjaId, ?int $galponId): Collection
    {
        if ($this->empresaContext->empresaIdFor($user) === null) {
            return new Collection;
        }

        $query = Galpon::query()
            ->with('granja')
            ->disponiblesParaCarga()
            ->orderBy('nombre');

        $query = $this->empresaScope->constrainQuery($query, $user);

        if ($granjaId !== null) {
            $query->where('granja_id', $granjaId);
        }

        if ($galponId !== null) {
            $query->where('id', $galponId);
        }

        return $query->get();
    }

    /**
     * @return array{huevos: int, huevos_descarte: int, muertes: int, descarte_aves: int, alimento_kg: float}
     */
    private function vacios(): array
    {
        return [
            'huevos' => 0,
            'huevos_descarte' => 0,
            'muertes' => 0,
            'descarte_aves' => 0,
            'alimento_kg' => 0.0,
        ];
    }
}
