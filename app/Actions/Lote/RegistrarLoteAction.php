<?php

namespace App\Actions\Lote;

use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RegistrarLoteAction
{
    /**
     * @param  array<string, int>  $cantidadesPorTipo  claves: valor de `TipoHuevo`
     * @return Collection<int, Lote>
     */
    public function execute(
        User $user,
        Galpon $galpon,
        array $cantidadesPorTipo,
        Carbon $fechaNacimiento,
        ?Carbon $fechaIngreso = null,
    ): Collection {
        Gate::forUser($user)->authorize('create', Lote::class);
        Gate::forUser($user)->authorize('view', $galpon);

        if ($user->empresa_id !== $galpon->empresa_id) {
            throw ValidationException::withMessages([
                'galponId' => 'El galpón no pertenece a tu empresa.',
            ]);
        }

        if (! $galpon->estado->permiteCarga() || ! $galpon->activo) {
            throw ValidationException::withMessages([
                'galponId' => 'El galpón no está disponible para carga.',
            ]);
        }

        if ($cantidadesPorTipo === []) {
            throw ValidationException::withMessages([
                'tiposHuevo' => 'Marcá al menos un tipo de ave.',
            ]);
        }

        foreach ($cantidadesPorTipo as $tipoValue => $cantidad) {
            if ($cantidad < 1) {
                throw ValidationException::withMessages([
                    'cantidad_'.$tipoValue => 'La cantidad debe ser mayor a cero.',
                ]);
            }
        }

        if ($fechaNacimiento->isFuture()) {
            throw ValidationException::withMessages([
                'fechaNacimiento' => 'La fecha de nacimiento no puede ser futura.',
            ]);
        }

        $fechaIngreso ??= Carbon::today();

        return DB::transaction(function () use ($user, $galpon, $cantidadesPorTipo, $fechaNacimiento, $fechaIngreso): Collection {
            /** @var Galpon $galponBloqueado */
            $galponBloqueado = Galpon::query()
                ->whereKey($galpon->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lotesCreados = new Collection;

            foreach ($cantidadesPorTipo as $tipoValue => $cantidad) {
                $tipo = TipoHuevo::from($tipoValue);
                $codigo = $this->generarCodigo($galponBloqueado, $tipo, $fechaIngreso);

                $lote = Lote::query()->create([
                    'empresa_id' => $user->empresa_id,
                    'galpon_id' => $galponBloqueado->id,
                    'codigo' => $codigo,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'fecha_ingreso' => $fechaIngreso,
                    'cantidad_inicial' => $cantidad,
                    'tipo_huevo' => $tipo,
                    'estado' => LoteEstado::Activo,
                ]);

                $galponBloqueado->increment('aves_actuales', $cantidad);
                $lotesCreados->push($lote);
            }

            return $lotesCreados;
        });
    }

    private function generarCodigo(Galpon $galpon, TipoHuevo $tipo, Carbon $fechaIngreso): string
    {
        $galponCodigo = $galpon->codigo !== null && $galpon->codigo !== ''
            ? $galpon->codigo
            : 'G'.$galpon->id;

        $prefijo = sprintf(
            '%s-%s-%s-',
            $galponCodigo,
            $fechaIngreso->format('Ymd'),
            $tipo->codigoLote(),
        );

        $secuencia = Lote::query()
            ->forEmpresa((int) $galpon->empresa_id)
            ->where('galpon_id', $galpon->id)
            ->where('codigo', 'like', $prefijo.'%')
            ->pluck('codigo')
            ->map(fn (string $codigo): int => (int) substr($codigo, strlen($prefijo)))
            ->max() ?? 0;

        return $prefijo.($secuencia + 1);
    }
}
