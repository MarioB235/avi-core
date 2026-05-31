<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'name' => fake()->name(),
            'documento' => (string) fake()->unique()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function adminAvicore(): static
    {
        return $this->state(fn (array $attributes) => [
            'empresa_id' => null,
            'rol' => UserRole::AdminAvicore,
        ]);
    }

    public function role(UserRole $rol): static
    {
        return $this->state(fn (array $attributes) => [
            'rol' => $rol,
        ]);
    }

    public function mustChangePassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'must_change_password' => true,
        ]);
    }
}
