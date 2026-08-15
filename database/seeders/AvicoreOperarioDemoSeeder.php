<?php

namespace Database\Seeders;

use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\VacunaTipo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Models\Vacunacion;
use Illuminate\Database\Seeder;

class AvicoreOperarioDemoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->where('codigo', 'DEMO')->first();

        if ($empresa === null) {
            return;
        }

        $galpon = Galpon::query()
            ->where('empresa_id', $empresa->id)
            ->where('codigo', 'G-01')
            ->first();

        if ($galpon === null) {
            return;
        }

        if (RegistroOperativo::query()->where('galpon_id', $galpon->id)->exists()) {
            return;
        }

        $operario = User::query()
            ->where('empresa_id', $empresa->id)
            ->where('documento', '000000000')
            ->first();

        if ($operario === null) {
            return;
        }

        $lote = Lote::query()
            ->where('galpon_id', $galpon->id)
            ->where('estado', LoteEstado::EnProduccion)
            ->first();

        RegistroOperativo::query()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galpon->id,
            'user_id' => $operario->id,
            'tipo' => RegistroOperativoTipo::Huevos,
            'huevos' => 1200,
            'huevos_descarte' => 30,
            'estado' => RegistroOperativoEstado::Activo,
            'created_at' => now()->setTime(8, 30),
            'updated_at' => now()->setTime(8, 30),
        ]);

        RegistroOperativo::query()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galpon->id,
            'user_id' => $operario->id,
            'tipo' => RegistroOperativoTipo::Muertes,
            'muertes' => 2,
            'estado' => RegistroOperativoEstado::Activo,
            'created_at' => now()->setTime(9, 0),
            'updated_at' => now()->setTime(9, 0),
        ]);

        RegistroOperativo::query()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galpon->id,
            'user_id' => $operario->id,
            'tipo' => RegistroOperativoTipo::Descarte,
            'descarte_aves' => 1,
            'estado' => RegistroOperativoEstado::Activo,
            'created_at' => now()->setTime(9, 45),
            'updated_at' => now()->setTime(9, 45),
        ]);

        RegistroOperativo::query()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galpon->id,
            'user_id' => $operario->id,
            'tipo' => RegistroOperativoTipo::Alimento,
            'alimento_kg' => 8500,
            'estado' => RegistroOperativoEstado::Activo,
            'created_at' => now()->subDays(2)->setTime(14, 0),
            'updated_at' => now()->subDays(2)->setTime(14, 0),
        ]);

        RegistroOperativo::query()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galpon->id,
            'user_id' => $operario->id,
            'tipo' => RegistroOperativoTipo::Huevos,
            'huevos' => 980,
            'huevos_descarte' => 12,
            'estado' => RegistroOperativoEstado::Activo,
            'created_at' => now()->subDay()->setTime(8, 15),
            'updated_at' => now()->subDay()->setTime(8, 15),
        ]);

        if ($lote !== null) {
            Vacunacion::query()->create([
                'empresa_id' => $empresa->id,
                'galpon_id' => $galpon->id,
                'lote_id' => $lote->id,
                'user_id' => $operario->id,
                'vacuna' => VacunaTipo::Gumboro,
                'estado' => RegistroOperativoEstado::Activo,
                'created_at' => now()->subDays(3)->setTime(10, 30),
                'updated_at' => now()->subDays(3)->setTime(10, 30),
            ]);
        }
    }
}
