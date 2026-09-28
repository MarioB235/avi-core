<?php

namespace App\Services;

use App\Enums\AuditoriaCategoria;
use App\Models\Auditoria;
use App\Models\User;
use App\Support\AdminAuditoriaConsultaFiltros;
use App\Support\AuditoriaPresentacion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class AdminAuditoriaConsultaService
{
    public function __construct(
        private EmpresaContextService $empresaContext,
        private SoporteEmpresaService $soporte,
    ) {}

    public function canView(User $user): bool
    {
        return $this->soporte->canViewAuditoria($user);
    }

    /**
     * @return Collection<int, User>
     */
    public function actoresParaFiltro(User $user): Collection
    {
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null) {
            return new Collection;
        }

        $actorIds = Auditoria::query()
            ->forEmpresa($empresaId)
            ->whereNotNull('actor_id')
            ->distinct()
            ->pluck('actor_id');

        if ($actorIds->isEmpty()) {
            return new Collection;
        }

        return User::query()
            ->whereIn('id', $actorIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return LengthAwarePaginator<int, Auditoria>
     */
    public function auditoriasPaginadas(
        User $user,
        AdminAuditoriaConsultaFiltros $filtros,
        int $perPage = 25,
        ?int $page = null,
    ): LengthAwarePaginator {
        $currentPage = max(1, $page ?? (int) request()->query('page', 1));
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null || ! $this->canView($user)) {
            return new LengthAwarePaginator([], 0, $perPage, $currentPage);
        }

        $query = Auditoria::query()
            ->forEmpresa($empresaId)
            ->with('actor')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        $this->aplicarFiltros($query, $filtros);

        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function resolverDetalle(User $user, int $auditoriaId): ?Auditoria
    {
        $empresaId = $this->empresaContext->empresaIdFor($user);

        if ($empresaId === null || ! $this->canView($user)) {
            return null;
        }

        return Auditoria::query()
            ->forEmpresa($empresaId)
            ->with('actor')
            ->find($auditoriaId);
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function detalleLineas(Auditoria $auditoria): array
    {
        return AuditoriaPresentacion::detalleLineas($auditoria);
    }

    /**
     * @return array<string, string>
     */
    public function categoriaOptions(): array
    {
        return collect(AuditoriaCategoria::cases())
            ->mapWithKeys(fn (AuditoriaCategoria $categoria): array => [
                $categoria->value => $categoria->label(),
            ])
            ->all();
    }

    private function aplicarFiltros(Builder $query, AdminAuditoriaConsultaFiltros $filtros): void
    {
        if ($filtros->categoria !== null && $filtros->categoria !== '') {
            $query->where('categoria', $filtros->categoria);
        }

        if ($filtros->actorId !== null) {
            $query->where('actor_id', $filtros->actorId);
        }

        if ($filtros->accion !== null && trim($filtros->accion) !== '') {
            $query->where('accion', 'like', '%'.trim($filtros->accion).'%');
        }

        if ($filtros->fechaDesde !== null && $filtros->fechaDesde !== '') {
            $query->where('occurred_at', '>=', Carbon::parse($filtros->fechaDesde)->startOfDay());
        }

        if ($filtros->fechaHasta !== null && $filtros->fechaHasta !== '') {
            $query->where('occurred_at', '<=', Carbon::parse($filtros->fechaHasta)->endOfDay());
        }
    }
}
