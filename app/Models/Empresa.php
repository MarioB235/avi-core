<?php

namespace App\Models;

use App\Enums\EmpresaEstado;
use App\Support\EmpresaConfiguracion;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'codigo', 'logo_path', 'estado', 'configuracion'])]
class Empresa extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'configuracion' => 'array',
            'estado' => EmpresaEstado::class,
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function granjas(): HasMany
    {
        return $this->hasMany(Granja::class);
    }

    public function galpones(): HasMany
    {
        return $this->hasMany(Galpon::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function registrosOperativos(): HasMany
    {
        return $this->hasMany(RegistroOperativo::class);
    }

    public function permiteLogin(): bool
    {
        return $this->estado->permiteLogin();
    }

    /**
     * @return list<array{estado_anterior: string, estado_nuevo: string, motivo: string, actor_id: int, actor_name: string, fecha: string}>
     */
    public function estadoHistorial(): array
    {
        $historial = $this->configuracion['estado_historial'] ?? [];

        return is_array($historial) ? $historial : [];
    }

    public function ultimoCambioEstado(): ?array
    {
        $historial = $this->estadoHistorial();

        if ($historial === []) {
            return null;
        }

        return $historial[array_key_last($historial)];
    }

    public function configuracionOperativa(): EmpresaConfiguracion
    {
        return EmpresaConfiguracion::fromEmpresa($this);
    }
}
