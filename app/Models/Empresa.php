<?php

namespace App\Models;

use App\Enums\EmpresaEstado;
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
}
