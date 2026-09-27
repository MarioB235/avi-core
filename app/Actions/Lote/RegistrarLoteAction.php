<?php

namespace App\Actions\Lote;

use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\User;
use App\Services\EmpresaRelationalGuard;
use App\Support\GalponValidacion;
use App\Support\LoteValidacion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RegistrarLoteAction
{
    public function __construct(private EmpresaRelationalGuard $relations) {}

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
        ?string $codigoSma = null,
    ): Collection {
        Gate::forUser($user)->authorize('create', Lote::class);
        Gate::forUser($user)->authorize('view', $galpon);

        $this->relations->assertGalponOfActor($user, $galpon, 'galponId');

        GalponValidacion::assertDisponibleParaCarga($galpon, 'galponId');
        GalponValidacion::assertCicloCerradoParaNuevoLote($galpon, 'galponId');

        LoteValidacion::assertCantidadesPorTipo($cantidadesPorTipo);
        LoteValidacion::assertFechaNacimiento($fechaNacimiento);

        $fechaIngreso ??= Carbon::today();

        LoteValidacion::assertFechaIngreso($fechaIngreso);
        LoteValidacion::assertFechasCoherentes($fechaNacimiento, $fechaIngreso);

        $codigoSma = LoteValidacion::assertCodigoSma($codigoSma);

        return DB::transaction(function () use ($user, $galpon, $cantidadesPorTipo, $fechaNacimiento, $fechaIngreso, $codigoSma): Collection {
            /** @var Galpon $galponBloqueado */
            $galponBloqueado = Galpon::query()
                ->with('granja')
                ->whereKey($galpon->id)
                ->lockForUpdate()
                ->firstOrFail();

            GalponValidacion::assertDisponibleParaCarga($galponBloqueado, 'galponId');
            GalponValidacion::assertCicloCerradoParaNuevoLote($galponBloqueado, 'galponId');

            $lotesCreados = new Collection;

            foreach ($cantidadesPorTipo as $tipoValue => $cantidad) {
                $tipo = TipoHuevo::from($tipoValue);
                $codigo = $this->generarCodigo($galponBloqueado, $tipo, $fechaIngreso);

                $lote = Lote::query()->create([
                    'empresa_id' => $user->empresa_id,
                    'galpon_id' => $galponBloqueado->id,
                    'codigo' => $codigo,
                    'codigo_sma' => $codigoSma,
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
