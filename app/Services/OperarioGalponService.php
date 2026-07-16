<?php

namespace App\Services;

use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use App\Support\OperarioHistorialItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OperarioGalponService
{
    /**
     * @return Collection<int, Galpon>
     */
    public function galponesDisponibles(User $user): Collection
    {
        if ($user->empresa_id === null) {
            return new Collection;
        }

        return Galpon::query()
            ->forEmpresa($user->empresa_id)
            ->disponiblesParaCarga()
            ->with('granja')
            ->orderBy('nombre')
            ->get();
    }

    public function galponActual(User $user): ?Galpon
    {
        if ($user->ultimo_galpon_id === null) {
            return null;
        }

        return $this->galponDisponibleParaUsuario($user, (int) $user->ultimo_galpon_id);
    }

    public function galponDisponibleParaUsuario(User $user, int $galponId): ?Galpon
    {
        if ($user->empresa_id === null) {
            return null;
        }

        $galpon = Galpon::query()
            ->forEmpresa((int) $user->empresa_id)
            ->disponiblesParaCarga()
            ->with('granja')
            ->find($galponId);

        if ($galpon === null) {
            return null;
        }

        Gate::forUser($user)->authorize('view', $galpon);

        return $galpon;
    }

    public function seleccionarGalpon(User $user, Galpon $galpon): void
    {
        Gate::forUser($user)->authorize('view', $galpon);

        if (! $galpon->estado->permiteCarga() || ! $galpon->activo) {
            throw ValidationException::withMessages([
                'galpon_id' => 'El galpón seleccionado no está disponible para carga.',
            ]);
        }

        $user->forceFill(['ultimo_galpon_id' => $galpon->id])->save();
    }

    /**
     * Historial del operario: todos los tipos activos, orden cronológico descendente (más reciente primero).
     */
    public function historialCargasQuery(User $user, ?string $fecha = null): Builder
    {
        if ($user->empresa_id === null) {
            return RegistroOperativo::query()->whereRaw('1 = 0');
        }

        return RegistroOperativo::query()
            ->forEmpresa($user->empresa_id)
            ->where('user_id', $user->id)
            ->activos()
            ->enFecha($fecha)
            ->orderByDesc('created_at');
    }

    public function etiquetaGalpon(?Galpon $galpon): string
    {
        if ($galpon === null) {
            return 'Sin seleccionar';
        }

        return $galpon->displayName();
    }

    /**
     * @return LengthAwarePaginator<int, OperarioHistorialItem>
     */
    public function historialPaginado(
        User $user,
        ?string $fecha = null,
        int $perPage = 20,
        ?int $page = null,
    ): LengthAwarePaginator {
        // avicore-defer: paginación en memoria (registros + vacunaciones), revisar si historial > ~500 ítems/usuario o latencia >300ms
        if ($user->empresa_id === null) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $registros = $this->historialCargasQuery($user, $fecha)
            ->with('galpon')
            ->get()
            ->map(fn (RegistroOperativo $registro): OperarioHistorialItem => OperarioHistorialItem::fromRegistro($registro));

        $vacunaciones = Vacunacion::query()
            ->forEmpresa($user->empresa_id)
            ->where('user_id', $user->id)
            ->activos()
            ->enFecha($fecha)
            ->with(['lote', 'galpon'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Vacunacion $vacunacion): OperarioHistorialItem => OperarioHistorialItem::fromVacunacion($vacunacion));

        $items = $registros
            ->concat($vacunaciones)
            ->sortByDesc(fn (OperarioHistorialItem $item) => $item->createdAt->timestamp)
            ->values();

        $currentPage = max(1, $page ?? (int) request()->query('page', 1));
        $total = $items->count();
        $slice = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()],
        );
    }
}
