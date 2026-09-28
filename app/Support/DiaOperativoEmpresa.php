<?php

namespace App\Support;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class DiaOperativoEmpresa
{
    public function __construct(
        public readonly string $zonaHoraria,
        public readonly Carbon $fechaLogica,
    ) {}

    public static function forEmpresa(Empresa|int $empresa, ?Carbon $referencia = null): self
    {
        $model = is_int($empresa)
            ? Empresa::query()->findOrFail($empresa)
            : $empresa;

        $config = EmpresaConfiguracion::fromEmpresa($model);
        $referencia ??= now();

        $fechaLogica = $referencia
            ->copy()
            ->timezone($config->zonaHoraria)
            ->startOfDay();

        return new self($config->zonaHoraria, $fechaLogica);
    }

    public static function hoyParaEmpresa(Empresa|int $empresa): self
    {
        return self::forEmpresa($empresa);
    }

    public static function ayerParaEmpresa(Empresa|int $empresa): self
    {
        $hoy = self::hoyParaEmpresa($empresa);

        return self::forEmpresa($empresa, $hoy->fechaLogica->copy()->subDay());
    }

    public static function enFechaParaEmpresa(Empresa|int $empresa, string $fechaYmd): self
    {
        $model = is_int($empresa)
            ? Empresa::query()->findOrFail($empresa)
            : $empresa;

        $config = EmpresaConfiguracion::fromEmpresa($model);

        $fechaLogica = Carbon::parse($fechaYmd, $config->zonaHoraria)->startOfDay();

        return new self($config->zonaHoraria, $fechaLogica);
    }

    public static function fechaLogicaHoy(Empresa|int $empresa): string
    {
        return self::hoyParaEmpresa($empresa)->fechaLogica->toDateString();
    }

    public static function esDiaOperativoActual(int $empresaId, ?Carbon $instant): bool
    {
        if ($instant === null) {
            return false;
        }

        return self::hoyParaEmpresa($empresaId)->contieneInstante($instant);
    }

    public function contieneInstante(?Carbon $instant): bool
    {
        if ($instant === null) {
            return false;
        }

        $instanteUtc = $instant->copy()->utc();

        return $instanteUtc->gte($this->inicioUtc()) && $instanteUtc->lt($this->finUtc());
    }

    public function inicioUtc(): Carbon
    {
        return $this->fechaLogica->copy()->utc();
    }

    public function finUtc(): Carbon
    {
        return $this->fechaLogica->copy()->addDay()->utc();
    }

    public function aplicarAlQuery(Builder $query, string $column = 'created_at'): Builder
    {
        return $query
            ->where($column, '>=', $this->inicioUtc())
            ->where($column, '<', $this->finUtc());
    }
}
