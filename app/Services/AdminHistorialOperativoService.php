<?php

namespace App\Services;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\UserRole;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use App\Support\AdminHistorialOperativoFiltros;
use App\Support\DiaOperativoEmpresa;
use App\Support\SupervisorHistorialItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminHistorialOperativoService
{
    public function __construct(
        private EmpresaContextService $empresaContext,
        private SoporteEmpresaService $soporte,
        private TotalesCapturaDiaService $totalesCapturaDia,
    ) {}

    /**
     * Totales del día operativo actual (solo registros activos, mismo scope que Resumen/Inicio).
     *
     * @return array{
     *     huevos: int,
     *     huevos_descarte: int,
     *     muertes: int,
     *     descarte_aves: int,
     *     alimento_kg: float,
     * }
     */
    public function totalesCapturaDiaActiva(User $user, ?int $granjaId = null, ?int $galponId = null): array
    {
        return $this->totalesCapturaDia->paraUsuario($user, $granjaId, $galponId);
    }

    public function canView(User $user): bool
    {
        return $this->soporte->canViewResumenOperativo($user);
    }

    /**
     * @return Collection<int, Granja>
     */
    public function granjasParaFiltro(User $user): Collection
    {
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return new Collection;
        }

        return Granja::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'dicose']);
    }

    /**
     * @return Collection<int, Galpon>
     */
    public function galponesParaFiltro(User $user, ?int $granjaId = null): Collection
    {
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return new Collection;
        }

        return Galpon::query()
            ->forEmpresa($empresaId)
            ->with('granja')
            ->when($granjaId !== null, fn (Builder $query): Builder => $query->where('granja_id', $granjaId))
            ->orderBy('nombre')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function operariosParaFiltro(User $user): Collection
    {
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return new Collection;
        }

        return User::query()
            ->where('empresa_id', $empresaId)
            ->where('rol', UserRole::Operario)
            ->where('activo', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return LengthAwarePaginator<int, SupervisorHistorialItem>
     */
    public function historialPaginado(
        User $user,
        AdminHistorialOperativoFiltros $filtros,
        int $perPage = 25,
        ?int $page = null,
    ): LengthAwarePaginator {
        $currentPage = max(1, $page ?? (int) request()->query('page', 1));

        if (! $this->canView($user)) {
            return new LengthAwarePaginator([], 0, $perPage, $currentPage);
        }

        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return new LengthAwarePaginator([], 0, $perPage, $currentPage);
        }

        $incluirRegistros = $filtros->tipo === null || $filtros->tipo !== 'vacunacion';
        $incluirVacunaciones = $filtros->tipo === null || $filtros->tipo === 'vacunacion';

        $registrosSub = $incluirRegistros
            ? $this->registrosQuery($empresaId, $filtros)
                ->reorder()
                ->selectRaw("id, 'registro' as source_type, created_at")
            : RegistroOperativo::query()->whereRaw('1 = 0')->selectRaw("id, 'registro' as source_type, created_at");

        $vacunacionesSub = $incluirVacunaciones
            ? $this->vacunacionesQuery($empresaId, $filtros)
                ->reorder()
                ->selectRaw("id, 'vacunacion' as source_type, created_at")
            : Vacunacion::query()->whereRaw('1 = 0')->selectRaw("id, 'vacunacion' as source_type, created_at");

        $union = $registrosSub->unionAll($vacunacionesSub);

        $total = (int) DB::query()->fromSub($union, 'historial_union')->count();

        $rows = DB::query()
            ->fromSub($union, 'historial_union')
            ->orderByDesc('created_at')
            ->forPage($currentPage, $perPage)
            ->get();

        $registroIds = $rows->where('source_type', 'registro')->pluck('id');
        $vacunacionIds = $rows->where('source_type', 'vacunacion')->pluck('id');

        $registrosById = $registroIds->isEmpty()
            ? collect()
            : RegistroOperativo::query()
                ->whereIn('id', $registroIds)
                ->with(['galpon', 'user'])
                ->get()
                ->keyBy('id');

        $vacunacionesById = $vacunacionIds->isEmpty()
            ? collect()
            : Vacunacion::query()
                ->whereIn('id', $vacunacionIds)
                ->with(['lote', 'galpon', 'user'])
                ->get()
                ->keyBy('id');

        $items = $rows
            ->map(function (object $row) use ($registrosById, $vacunacionesById): ?SupervisorHistorialItem {
                if ($row->source_type === 'registro') {
                    $registro = $registrosById->get($row->id);

                    return $registro !== null
                        ? SupervisorHistorialItem::fromRegistro($registro)
                        : null;
                }

                $vacunacion = $vacunacionesById->get($row->id);

                return $vacunacion !== null
                    ? SupervisorHistorialItem::fromVacunacion($vacunacion)
                    : null;
            })
            ->filter()
            ->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }

    /**
     * @return Builder<RegistroOperativo>
     */
    private function registrosQuery(int $empresaId, AdminHistorialOperativoFiltros $filtros): Builder
    {
        $query = RegistroOperativo::query()
            ->forEmpresa($empresaId)
            ->orderByDesc('created_at');

        if ($filtros->galponId !== null) {
            $query->where('galpon_id', $filtros->galponId);
        } elseif ($filtros->granjaId !== null) {
            $query->whereHas('galpon', fn (Builder $galpon): Builder => $galpon->where('granja_id', $filtros->granjaId));
        }

        if ($filtros->userId !== null) {
            $query->where('user_id', $filtros->userId);
        }

        if ($filtros->tipo !== null && $filtros->tipo !== 'vacunacion') {
            $tipo = RegistroOperativoTipo::tryFrom($filtros->tipo);

            if ($tipo !== null) {
                $query->where('tipo', $tipo);
            }
        }

        if ($filtros->estado === RegistroOperativoEstado::Activo->value) {
            $query->where('estado', RegistroOperativoEstado::Activo);
        } elseif ($filtros->estado === RegistroOperativoEstado::Anulado->value) {
            $query->where('estado', RegistroOperativoEstado::Anulado);
        }

        $this->aplicarRangoFechas($query, $empresaId, $filtros);

        return $query;
    }

    /**
     * @return Builder<Vacunacion>
     */
    private function vacunacionesQuery(int $empresaId, AdminHistorialOperativoFiltros $filtros): Builder
    {
        $query = Vacunacion::query()
            ->forEmpresa($empresaId)
            ->orderByDesc('created_at');

        if ($filtros->galponId !== null) {
            $query->where('galpon_id', $filtros->galponId);
        } elseif ($filtros->granjaId !== null) {
            $query->whereHas('galpon', fn (Builder $galpon): Builder => $galpon->where('granja_id', $filtros->granjaId));
        }

        if ($filtros->userId !== null) {
            $query->where('user_id', $filtros->userId);
        }

        if ($filtros->estado === RegistroOperativoEstado::Activo->value) {
            $query->where('estado', RegistroOperativoEstado::Activo);
        } elseif ($filtros->estado === RegistroOperativoEstado::Anulado->value) {
            $query->where('estado', RegistroOperativoEstado::Anulado);
        }

        $this->aplicarRangoFechas($query, $empresaId, $filtros);

        return $query;
    }

    /**
     * @param  Builder<RegistroOperativo>|Builder<Vacunacion>  $query
     */
    private function aplicarRangoFechas(Builder $query, int $empresaId, AdminHistorialOperativoFiltros $filtros): void
    {
        if ($filtros->fechaDesde !== null && $filtros->fechaDesde !== '') {
            $desde = DiaOperativoEmpresa::enFechaParaEmpresa($empresaId, $filtros->fechaDesde);
            $query->where('created_at', '>=', $desde->inicioUtc());
        }

        if ($filtros->fechaHasta !== null && $filtros->fechaHasta !== '') {
            $hasta = DiaOperativoEmpresa::enFechaParaEmpresa($empresaId, $filtros->fechaHasta);
            $query->where('created_at', '<', $hasta->finUtc());
        }
    }
}
