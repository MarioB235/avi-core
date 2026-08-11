<?php

namespace Database\Factories;

use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistroOperativo>
 */
class RegistroOperativoFactory extends Factory
{
    protected $model = RegistroOperativo::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'galpon_id' => Galpon::factory(),
            'user_id' => User::factory(),
            'tipo' => RegistroOperativoTipo::Huevos,
            'huevos' => null,
            'huevos_descarte' => null,
            'muertes' => null,
            'descarte_aves' => null,
            'alimento_kg' => null,
            'observacion' => null,
            'estado' => RegistroOperativoEstado::Activo,
            'anulado_at' => null,
            'anulado_por' => null,
            'motivo_anulacion' => null,
        ];
    }

    public function forGalponAndUser(Galpon $galpon, User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => $galpon->empresa_id,
            'galpon_id' => $galpon->id,
            'user_id' => $user->id,
        ]);
    }
}
