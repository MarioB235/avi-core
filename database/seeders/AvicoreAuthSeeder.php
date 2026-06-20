<?php

namespace Database\Seeders;

use App\Enums\EmpresaEstado;
use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;

class AvicoreAuthSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->create([
            'nombre' => 'Avícola Demo',
            'codigo' => 'DEMO',
            'estado' => EmpresaEstado::Activa,
        ]);

        User::query()->create([
            'empresa_id' => null,
            'name' => 'Admin AviCore',
            'documento' => '900000001',
            'email' => 'admin@avicore.local',
            'password' => 'Avicore2026!',
            'rol' => UserRole::AdminAvicore,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::query()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Dueño Demo',
            'documento' => '100000001',
            'email' => 'dueno@demo.local',
            'password' => 'Avicore2026!',
            'rol' => UserRole::Dueno,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::query()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Administrativo Demo',
            'documento' => '300000001',
            'email' => 'administrativo@demo.local',
            'password' => 'Avicore2026!',
            'rol' => UserRole::Administrativo,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::query()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Encargado Demo',
            'documento' => '400000001',
            'email' => 'encargado@demo.local',
            'password' => 'Avicore2026!',
            'rol' => UserRole::Encargado,
            'activo' => true,
            'must_change_password' => false,
        ]);

        User::query()->create([
            'empresa_id' => $empresa->id,
            'name' => 'Operario Demo',
            'documento' => '200000001',
            'email' => null,
            'password' => 'Avicore2026!',
            'rol' => UserRole::Operario,
            'activo' => true,
            'must_change_password' => false,
        ]);
    }
}
