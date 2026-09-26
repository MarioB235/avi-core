<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Database\Seeders\AvicoreEquipoDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvicoreEquipoDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_demo_team_members_for_demo_company(): void
    {
        $empresa = Empresa::factory()->create(['codigo' => 'DEMO']);

        User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Dueno,
            'documento' => '000000000',
        ]);

        $this->seed(AvicoreEquipoDemoSeeder::class);

        $this->assertSame(7, User::query()->where('empresa_id', $empresa->id)->where('activo', true)->count());
        $this->assertDatabaseHas('users', [
            'empresa_id' => $empresa->id,
            'documento' => '11111111',
            'name' => 'María López',
            'rol' => UserRole::Operario->value,
        ]);
        $this->assertDatabaseHas('users', [
            'empresa_id' => $empresa->id,
            'documento' => '66666666',
            'name' => 'Laura Fernández',
            'rol' => UserRole::Administrativo->value,
        ]);
    }

    public function test_seeder_is_idempotent(): void
    {
        $empresa = Empresa::factory()->create(['codigo' => 'DEMO']);

        $this->seed(AvicoreEquipoDemoSeeder::class);
        $this->seed(AvicoreEquipoDemoSeeder::class);

        $this->assertSame(6, User::query()->where('empresa_id', $empresa->id)->count());
    }
}
