<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\SoporteSesion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SoporteSesion>
 */
class SoporteSesionFactory extends Factory
{
    protected $model = SoporteSesion::class;

    public function definition(): array
    {
        $started = now();

        return [
            'empresa_id' => Empresa::factory(),
            'actor_id' => User::factory()->adminAvicore(),
            'motivo' => 'Diagnóstico de acceso solicitado por el cliente.',
            'started_at' => $started,
            'expires_at' => $started->copy()->addHours(2),
            'ended_at' => null,
            'end_reason' => null,
        ];
    }

    public function ended(string $reason = 'manual'): static
    {
        return $this->state(fn (array $attributes): array => [
            'ended_at' => now(),
            'end_reason' => $reason,
        ]);
    }
}
