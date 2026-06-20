<?php

namespace Database\Factories;

use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Lote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lote>
 */
class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition(): array
    {
        $fechaIngreso = fake()->dateTimeBetween('-6 months', '-1 month');

        return [
            'empresa_id' => Empresa::factory(),
            'galpon_id' => Galpon::factory(),
            'codigo' => fake()->unique()->bothify('L-####'),
            'fecha_nacimiento' => (clone $fechaIngreso)->modify('-120 days'),
            'fecha_ingreso' => $fechaIngreso,
            'cantidad_inicial' => fake()->numberBetween(3000, 10000),
            'linea_raza' => 'Hy-Line',
            'tipo_huevo' => TipoHuevo::Blanco,
            'estado' => LoteEstado::EnProduccion,
            'observacion' => null,
        ];
    }

    public function forGalpon(Galpon $galpon): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => $galpon->empresa_id,
            'galpon_id' => $galpon->id,
        ]);
    }
}
