<?php

namespace App\Services;

use App\Models\SoporteSesion;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;

class SoporteEmpresaService
{
    private const SESSION_KEY = 'avicore.soporte_sesion_id';

    public function sessionId(): ?int
    {
        $id = session(self::SESSION_KEY);

        return is_numeric($id) ? (int) $id : null;
    }

    public function isActive(): bool
    {
        return $this->activeSesion() !== null;
    }

    public function empresaId(): ?int
    {
        return $this->activeSesion()?->empresa_id;
    }

    public function canViewResumenOperativo(User $user): bool
    {
        if ($user->isAdminAvicore()) {
            return $this->isActive();
        }

        return $user->empresa_id !== null && $user->rol->canViewResumen();
    }

    public function blocksProductionMutations(User $user): bool
    {
        return $user->isAdminAvicore() && $this->isActive();
    }

    public function activeSesion(): ?SoporteSesion
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $user->isAdminAvicore()) {
            return null;
        }

        $sessionId = $this->sessionId();

        if ($sessionId === null) {
            return null;
        }

        $sesion = SoporteSesion::query()
            ->with('empresa')
            ->find($sessionId);

        if ($sesion === null || $sesion->actor_id !== $user->id) {
            $this->clearSession();

            return null;
        }

        if ($sesion->ended_at !== null) {
            $this->clearSession();

            return null;
        }

        if ($sesion->expires_at->isPast()) {
            $this->endSesionRecord($sesion, 'expired');

            return null;
        }

        return $sesion;
    }

    public function rememberSession(SoporteSesion $sesion): void
    {
        session([self::SESSION_KEY => $sesion->id]);
    }

    public function clearSession(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @return array{
     *     empresa_nombre: string,
     *     motivo: string,
     *     expires_label: string,
     *     salir_route: string
     * }|null
     */
    public function bannerFor(User $user): ?array
    {
        $sesion = $this->activeSesion();

        if ($sesion === null || ! $user->isAdminAvicore()) {
            return null;
        }

        return [
            'empresa_nombre' => $sesion->empresa->nombre,
            'motivo' => $sesion->motivo,
            'expires_label' => $sesion->expires_at->timezone(config('app.timezone'))->format('H:i'),
            'salir_route' => route('avicore.soporte.finalizar'),
        ];
    }

    public function expiresAtForNewSession(): CarbonImmutable
    {
        $minutes = max(15, (int) config('avicore.soporte.duracion_minutos', 120));

        return CarbonImmutable::now()->addMinutes($minutes);
    }

    public function endSesionRecord(SoporteSesion $sesion, string $reason): void
    {
        if ($sesion->ended_at !== null) {
            return;
        }

        $this->recordAccion($sesion, 'fin', [
            'reason' => $reason,
            'empresa_id' => $sesion->empresa_id,
        ]);

        $sesion->update([
            'ended_at' => now(),
            'end_reason' => $reason,
        ]);

        if ($this->sessionId() === $sesion->id) {
            $this->clearSession();
        }
    }

    public function closeOpenSessionsForActor(User $actor, string $reason): void
    {
        $abiertas = SoporteSesion::query()
            ->where('actor_id', $actor->id)
            ->whereNull('ended_at')
            ->get();

        foreach ($abiertas as $sesion) {
            $this->endSesionRecord($sesion, $reason);
        }

        $this->clearSession();
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function recordAccion(SoporteSesion $sesion, string $tipo, array $meta = []): void
    {
        $acciones = $sesion->acciones ?? [];

        $acciones[] = array_merge([
            'tipo' => $tipo,
            'at' => now()->toIso8601String(),
        ], $meta);

        if (count($acciones) > 200) {
            $acciones = array_slice($acciones, -200);
        }

        $sesion->update(['acciones' => $acciones]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function recordAccionForActiveSession(string $tipo, array $meta = []): void
    {
        $sesion = $this->activeSesion();

        if ($sesion === null) {
            return;
        }

        $this->recordAccion($sesion, $tipo, $meta);
    }

    public function entryRouteName(): string
    {
        $route = (string) config('avicore.soporte.destino_entrada', 'avicore.resumen.index');

        return Route::has($route) ? $route : 'avicore.resumen.index';
    }

    public function resolveExitRoute(?string $routeName = null): string
    {
        /** @var list<string> $allowed */
        $allowed = config('avicore.soporte.destinos_salida', ['avicore.empresas.index']);
        $default = (string) config('avicore.soporte.destino_salida_default', 'avicore.empresas.index');

        if ($routeName !== null
            && in_array($routeName, $allowed, true)
            && Route::has($routeName)) {
            return $routeName;
        }

        return Route::has($default) ? $default : 'avicore.empresas.index';
    }
}
