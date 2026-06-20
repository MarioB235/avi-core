<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'empresa_id',
    'ultimo_galpon_id',
    'name',
    'documento',
    'email',
    'password',
    'rol',
    'activo',
    'must_change_password',
    'last_login_at',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => UserRole::class,
            'activo' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function ultimoGalpon(): BelongsTo
    {
        return $this->belongsTo(Galpon::class, 'ultimo_galpon_id');
    }

    public function registrosOperativos(): HasMany
    {
        return $this->hasMany(RegistroOperativo::class);
    }

    public function isAdminAvicore(): bool
    {
        return $this->rol === UserRole::AdminAvicore;
    }

    public function homeRouteName(): string
    {
        return $this->rol->isOperario() ? 'operario.home' : 'admin.home';
    }
}
