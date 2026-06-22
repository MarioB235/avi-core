<?php

namespace App\Services;

use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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

        $galpon = Galpon::query()
            ->forEmpresa((int) $user->empresa_id)
            ->disponiblesParaCarga()
            ->with('granja')
            ->find($user->ultimo_galpon_id);

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
     * @return Collection<int, RegistroOperativo>
     */
    public function ultimasCargasDelDia(User $user): Collection
    {
        if ($user->empresa_id === null) {
            return new Collection;
        }

        return RegistroOperativo::query()
            ->forEmpresa($user->empresa_id)
            ->where('user_id', $user->id)
            ->activos()
            ->delDia()
            ->with('galpon')
            ->latest('created_at')
            ->limit(10)
            ->get();
    }

    public function cantidadCargasDelDia(User $user): int
    {
        if ($user->empresa_id === null) {
            return 0;
        }

        return RegistroOperativo::query()
            ->forEmpresa($user->empresa_id)
            ->where('user_id', $user->id)
            ->activos()
            ->delDia()
            ->count();
    }

    public function maplesProducidosHoy(User $user): int
    {
        if ($user->empresa_id === null) {
            return 0;
        }

        $huevos = RegistroOperativo::query()
            ->forEmpresa($user->empresa_id)
            ->where('user_id', $user->id)
            ->activos()
            ->delDia()
            ->sum('huevos');

        return intdiv((int) $huevos, 30);
    }

    public function etiquetaGalpon(?Galpon $galpon): string
    {
        if ($galpon === null) {
            return 'Sin seleccionar';
        }

        return $galpon->displayName();
    }
}
