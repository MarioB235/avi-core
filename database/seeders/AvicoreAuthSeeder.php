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
        $empresa = Empresa::query()->firstOrCreate(
            ['codigo' => 'DEMO'],
            [
                'nombre' => 'Avícola Demo',
                'estado' => EmpresaEstado::Activa,
            ],
        );

        User::query()->firstOrCreate(
            [
                'empresa_id' => $empresa->id,
                'documento' => '000000000',
            ],
            [
                'name' => 'Usuario Prueba',
                'email' => 'prueba@demo.local',
                'password' => 'Avicore2026!',
                'rol' => UserRole::Dueno,
                'activo' => true,
                'must_change_password' => false,
            ],
        );
    }
}
