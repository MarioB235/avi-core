<?php

namespace Database\Factories;

use App\Enums\GalponEstado;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Galpon>
 */
class GalponFactory extends Factory
{
    protected $model = Galpon::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'granja_id' => Granja::factory(),
            'nombre' => 'Galpón '.fake()->unique()->numerify('##'),
            'codigo' => fake()->unique()->bothify('G-##'),
            'capacidad' => fake()->numberBetween(5000, 15000),
            'estado' => GalponEstado::Activo,
            'activo' => true,
            'aves_actuales' => fake()->numberBetween(3000, 12000),
            'observacion' => null,
        ];
    }

    public function forGranja(Granja $granja): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => $granja->empresa_id,
            'granja_id' => $granja->id,
        ]);
    }
}
