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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class OperarioGalponService
{
    /** @var array<string, mixed> */
    private array $memo = [];

    /**
     * @return Collection<int, Galpon>
     */
    public function galponesDisponibles(User $user): Collection
    {
        $memoKey = 'galpones_'.$user->id;

        if (isset($this->memo[$memoKey])) {
            /** @var Collection<int, Galpon> $cached */
            $cached = $this->memo[$memoKey];

            return $cached;
        }

        if ($user->empresa_id === null) {
            return $this->memo[$memoKey] = new Collection;
        }

        return $this->memo[$memoKey] = Galpon::query()
            ->forEmpresa($user->empresa_id)
            ->disponiblesParaCarga()
            ->with('granja')
            ->orderBy('nombre')
            ->get();
    }

    public function galponActual(User $user): ?Galpon
    {
        $memoKey = 'galpon_actual_'.$user->id;

        if (array_key_exists($memoKey, $this->memo)) {
            $cached = $this->memo[$memoKey];

            if ($cached === null) {
                return null;
            }

            $stillAvailable = $this->galponDisponibleParaUsuario($user, $cached->id);

            return $this->memo[$memoKey] = $stillAvailable;
        }

        if ($user->ultimo_galpon_id === null) {
            return $this->memo[$memoKey] = null;
        }

        return $this->memo[$memoKey] = $this->galponDisponibleParaUsuario($user, (int) $user->ultimo_galpon_id);
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

        unset($this->memo['galpon_actual_'.$user->id]);
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
     * avicore-defer: unificar count + página en una sola query SQL si el historial crece y el triple round-trip pesa.
     *
     * @return LengthAwarePaginator<int, OperarioHistorialItem>
     */
    public function historialPaginado(
        User $user,
        ?string $fecha = null,
        int $perPage = 20,
        ?int $page = null,
    ): LengthAwarePaginator {
        $currentPage = max(1, $page ?? (int) request()->query('page', 1));

        if ($user->empresa_id === null) {
            return new LengthAwarePaginator([], 0, $perPage, $currentPage);
        }

        $registrosSub = $this->historialCargasQuery($user, $fecha)
            ->reorder()
            ->selectRaw("id, 'registro' as source_type, created_at");

        $vacunacionesSub = Vacunacion::query()
            ->forEmpresa($user->empresa_id)
            ->where('user_id', $user->id)
            ->enFecha($fecha)
            ->selectRaw("id, 'vacunacion' as source_type, created_at");

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
                ->with('galpon')
                ->get()
                ->keyBy('id');

        $vacunacionesById = $vacunacionIds->isEmpty()
            ? collect()
            : Vacunacion::query()
                ->whereIn('id', $vacunacionIds)
                ->with(['lote', 'galpon'])
                ->get()
                ->keyBy('id');

        $items = $rows
            ->map(function (object $row) use ($registrosById, $vacunacionesById, $user): ?OperarioHistorialItem {
                if ($row->source_type === 'registro') {
                    $registro = $registrosById->get($row->id);

                    return $registro !== null
                        ? OperarioHistorialItem::fromRegistro($registro, $user)
                        : null;
                }

                $vacunacion = $vacunacionesById->get($row->id);

                return $vacunacion !== null
                    ? OperarioHistorialItem::fromVacunacion($vacunacion, $user)
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
}
