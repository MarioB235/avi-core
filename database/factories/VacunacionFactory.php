<?php

namespace Database\Factories;

use App\Enums\RegistroOperativoEstado;
use App\Enums\VacunaTipo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacunacion>
 */
class VacunacionFactory extends Factory
{
    protected $model = Vacunacion::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'galpon_id' => Galpon::factory(),
            'lote_id' => Lote::factory(),
            'user_id' => User::factory(),
            'vacuna' => fake()->randomElement(VacunaTipo::cases()),
            'observacion' => null,
            'estado' => RegistroOperativoEstado::Activo,
        ];
    }

    public function forLote(Lote $lote, User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => $lote->empresa_id,
            'galpon_id' => $lote->galpon_id,
            'lote_id' => $lote->id,
            'user_id' => $user->id,
        ]);
    }
}
