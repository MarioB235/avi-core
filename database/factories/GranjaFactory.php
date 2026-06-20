<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Granja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Granja>
 */
class GranjaFactory extends Factory
{
    protected $model = Granja::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'nombre' => 'Granja '.fake()->unique()->word(),
            'codigo' => fake()->unique()->bothify('GR-###'),
            'ubicacion' => fake()->city(),
            'activa' => true,
        ];
    }
}
