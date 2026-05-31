<?php

namespace Database\Factories;

use App\Enums\EmpresaEstado;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'codigo' => fake()->unique()->bothify('???-###'),
            'estado' => EmpresaEstado::Activa,
        ];
    }
}
