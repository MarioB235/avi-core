<?php

namespace Database\Seeders;

use App\Enums\LoteEstado;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Enums\TipoHuevo;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AvicoreDuenoDemoSeeder extends Seeder
{
    /**
     * Datos demo para panel Dueño: segundo galpón con lote, historial semanal y cargas de hoy.
     */
    public function run(): void
    {
        $empresa = Empresa::query()->where('codigo', 'DEMO')->first();

        if ($empresa === null) {
            return;
        }

        $galponUno = Galpon::query()
            ->where('empresa_id', $empresa->id)
            ->where('codigo', 'G-01')
            ->first();

        $galponDos = Galpon::query()
            ->where('empresa_id', $empresa->id)
            ->where('codigo', 'G-02')
            ->first();

        if ($galponUno === null || $galponDos === null) {
            return;
        }

        $operario = User::query()
            ->where('empresa_id', $empresa->id)
            ->where('documento', '000000000')
            ->first();

        if ($operario === null) {
            return;
        }

        $this->ensureLoteGalponDos($empresa->id, $galponDos->id);
        $this->ensurePosturaSemanal($empresa->id, $galponUno, $galponDos, $operario->id);
        $this->ensureCargaHoyGalponDos($empresa->id, $galponDos->id, $operario->id);
    }

    private function ensureLoteGalponDos(int $empresaId, int $galponId): void
    {
        if (Lote::query()->where('galpon_id', $galponId)->exists()) {
            return;
        }

        Lote::query()->create([
            'empresa_id' => $empresaId,
            'galpon_id' => $galponId,
            'codigo' => 'L-2026-02',
            'codigo_sma' => 'L-2024-102',
            'fecha_nacimiento' => now()->subMonths(5)->toDateString(),
            'fecha_ingreso' => now()->subMonths(4)->toDateString(),
            'cantidad_inicial' => 9800,
            'linea_raza' => 'Hy-Line Brown',
            'tipo_huevo' => TipoHuevo::Color,
            'estado' => LoteEstado::EnProduccion,
        ]);
    }

    private function ensurePosturaSemanal(
        int $empresaId,
        Galpon $galponUno,
        Galpon $galponDos,
        int $userId,
    ): void {
        $serie = [
            6 => [1020, 880],
            5 => [1080, 910],
            4 => [1110, 940],
            3 => [1095, 925],
            2 => [1140, 960],
            1 => [980, 850],
        ];

        foreach ($serie as $diasAtras => [$huevosUno, $huevosDos]) {
            $fecha = now()->subDays($diasAtras)->startOfDay();

            $this->ensureHuevosEnFecha($empresaId, $galponUno->id, $userId, $fecha, $huevosUno, 18);
            $this->ensureHuevosEnFecha($empresaId, $galponDos->id, $userId, $fecha, $huevosDos, 14);
        }
    }

    private function ensureCargaHoyGalponDos(int $empresaId, int $galponId, int $userId): void
    {
        $existeHoy = RegistroOperativo::query()
            ->where('galpon_id', $galponId)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->whereDate('created_at', now())
            ->exists();

        if ($existeHoy) {
            return;
        }

        RegistroOperativo::query()->create([
            'empresa_id' => $empresaId,
            'galpon_id' => $galponId,
            'user_id' => $userId,
            'tipo' => RegistroOperativoTipo::Huevos,
            'huevos' => 920,
            'huevos_descarte' => 18,
            'estado' => RegistroOperativoEstado::Activo,
            'created_at' => now()->setTime(7, 45),
            'updated_at' => now()->setTime(7, 45),
        ]);
    }

    private function ensureHuevosEnFecha(
        int $empresaId,
        int $galponId,
        int $userId,
        Carbon $fecha,
        int $huevos,
        int $descarte,
    ): void {
        $existe = RegistroOperativo::query()
            ->where('galpon_id', $galponId)
            ->where('tipo', RegistroOperativoTipo::Huevos)
            ->whereDate('created_at', $fecha)
            ->exists();

        if ($existe) {
            return;
        }

        RegistroOperativo::query()->create([
            'empresa_id' => $empresaId,
            'galpon_id' => $galponId,
            'user_id' => $userId,
            'tipo' => RegistroOperativoTipo::Huevos,
            'huevos' => $huevos,
            'huevos_descarte' => $descarte,
            'estado' => RegistroOperativoEstado::Activo,
            'created_at' => $fecha->copy()->setTime(8, 20),
            'updated_at' => $fecha->copy()->setTime(8, 20),
        ]);
    }
}
