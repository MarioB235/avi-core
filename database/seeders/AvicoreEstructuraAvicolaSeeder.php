<?php

namespace Database\Seeders;

use App\Enums\GalponEstado;
use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Models\User;
use Illuminate\Database\Seeder;

class AvicoreEstructuraAvicolaSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = Empresa::query()->where('codigo', 'DEMO')->first();

        if ($empresa === null) {
            return;
        }

        if (Granja::query()->where('empresa_id', $empresa->id)->exists()) {
            return;
        }

        $granja = Granja::query()->create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Granja Norte',
            'codigo' => 'GR-NORTE',
            'ubicacion' => 'Zona demo',
            'activa' => true,
        ]);

        $galponUno = Galpon::query()->create([
            'empresa_id' => $empresa->id,
            'granja_id' => $granja->id,
            'nombre' => 'Galpón 1',
            'codigo' => 'G-01',
            'capacidad' => 12000,
            'estado' => GalponEstado::Activo,
            'activo' => true,
            'aves_actuales' => 10500,
        ]);

        Galpon::query()->create([
            'empresa_id' => $empresa->id,
            'granja_id' => $granja->id,
            'nombre' => 'Galpón 2',
            'codigo' => 'G-02',
            'capacidad' => 10000,
            'estado' => GalponEstado::Activo,
            'activo' => true,
            'aves_actuales' => 9800,
        ]);

        Lote::query()->create([
            'empresa_id' => $empresa->id,
            'galpon_id' => $galponUno->id,
            'codigo' => 'L-2026-01',
            'codigo_sma' => 'L-2024-089',
            'fecha_nacimiento' => now()->subMonths(5)->toDateString(),
            'fecha_ingreso' => now()->subMonths(4)->toDateString(),
            'cantidad_inicial' => 10500,
            'linea_raza' => 'Hy-Line Brown',
            'tipo_huevo' => TipoHuevo::Blanco,
            'estado' => LoteEstado::EnProduccion,
        ]);

        User::query()
            ->where('empresa_id', $empresa->id)
            ->where('documento', '000000000')
            ->update(['ultimo_galpon_id' => $galponUno->id]);
    }
}
