<?php

namespace Database\Factories;

use App\Enums\MovimientoAvesEstado;
use App\Enums\MovimientoAvesTipo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovimientoAves>
 */
class MovimientoAvesFactory extends Factory
{
    protected $model = MovimientoAves::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'tipo' => MovimientoAvesTipo::Entrada,
            'estado' => MovimientoAvesEstado::Activo,
            'galpon_origen_id' => null,
            'galpon_destino_id' => Galpon::factory(),
            'lote_id' => null,
            'cantidad' => fake()->numberBetween(10, 500),
            'ajuste_delta' => null,
            'motivo' => 'Movimiento de prueba',
            'registrado_por' => User::factory(),
            'fecha_efectiva' => now(),
            'reversa_de_id' => null,
            'reversado_por_id' => null,
            'metadata' => null,
        ];
    }

    public function forEmpresaContext(Empresa $empresa, User $actor, Galpon $galponOrigen, ?Galpon $galponDestino = null): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => $empresa->id,
            'registrado_por' => $actor->id,
            'galpon_origen_id' => $galponOrigen->id,
            'galpon_destino_id' => $galponDestino?->id,
        ]);
    }

    public function entrada(Galpon $destino, User $actor, int $cantidad = 100, ?Lote $lote = null): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => $destino->empresa_id,
            'tipo' => MovimientoAvesTipo::Entrada,
            'galpon_origen_id' => null,
            'galpon_destino_id' => $destino->id,
            'lote_id' => $lote?->id,
            'cantidad' => $cantidad,
            'ajuste_delta' => null,
            'registrado_por' => $actor->id,
            'motivo' => 'Entrada inicial',
        ]);
    }

    public function traslado(Galpon $origen, Galpon $destino, User $actor, int $cantidad = 50, ?Lote $lote = null): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => $origen->empresa_id,
            'tipo' => MovimientoAvesTipo::Traslado,
            'galpon_origen_id' => $origen->id,
            'galpon_destino_id' => $destino->id,
            'lote_id' => $lote?->id,
            'cantidad' => $cantidad,
            'ajuste_delta' => null,
            'registrado_por' => $actor->id,
            'motivo' => 'Traslado entre galpones',
        ]);
    }

    public function ajuste(Galpon $galpon, User $actor, int $delta): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => $galpon->empresa_id,
            'tipo' => MovimientoAvesTipo::Ajuste,
            'galpon_origen_id' => $galpon->id,
            'galpon_destino_id' => null,
            'cantidad' => abs($delta),
            'ajuste_delta' => $delta,
            'registrado_por' => $actor->id,
            'motivo' => 'Ajuste por conteo',
        ]);
    }
}
