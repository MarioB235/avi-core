<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;

class AvicoreEquipoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->where('codigo', 'DEMO')->first();

        if ($empresa === null) {
            return;
        }

        $personas = [
            [
                'documento' => '11111111',
                'name' => 'María López',
                'email' => 'maria.lopez@demo.local',
                'rol' => UserRole::Operario,
            ],
            [
                'documento' => '22222222',
                'name' => 'Carlos Pereira',
                'email' => 'carlos.pereira@demo.local',
                'rol' => UserRole::Operario,
            ],
            [
                'documento' => '33333333',
                'name' => 'Ana Rodríguez',
                'email' => 'ana.rodriguez@demo.local',
                'rol' => UserRole::Operario,
            ],
            [
                'documento' => '44444444',
                'name' => 'Diego Souza',
                'email' => 'diego.souza@demo.local',
                'rol' => UserRole::Reparto,
            ],
            [
                'documento' => '55555555',
                'name' => 'Roberto Méndez',
                'email' => 'roberto.mendez@demo.local',
                'rol' => UserRole::Encargado,
            ],
            [
                'documento' => '66666666',
                'name' => 'Laura Fernández',
                'email' => 'laura.fernandez@demo.local',
                'rol' => UserRole::Administrativo,
            ],
        ];

        foreach ($personas as $persona) {
            User::query()->firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'documento' => $persona['documento'],
                ],
                [
                    'name' => $persona['name'],
                    'email' => $persona['email'],
                    'password' => 'Avicore2026!',
                    'rol' => $persona['rol'],
                    'activo' => true,
                    'must_change_password' => false,
                ],
            );
        }
    }
}
