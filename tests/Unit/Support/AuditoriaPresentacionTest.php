<?php

namespace Tests\Unit\Support;

use App\Enums\AuditoriaCategoria;
use App\Models\Auditoria;
use App\Models\Empresa;
use App\Models\User;
use App\Support\AuditoriaPresentacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaPresentacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_detalle_incluye_metadata_formateada(): void
    {
        $empresa = Empresa::factory()->create();
        $actor = User::factory()->create(['empresa_id' => $empresa->id]);

        $auditoria = Auditoria::query()->create([
            'empresa_id' => $empresa->id,
            'actor_id' => $actor->id,
            'categoria' => AuditoriaCategoria::Lote,
            'accion' => 'estado_cambiado',
            'entidad_tipo' => 'lote',
            'entidad_id' => 1,
            'motivo' => 'Fin de ciclo',
            'metadata' => [
                'estado_anterior' => 'en_produccion',
                'estado_nuevo' => 'cerrado',
            ],
            'occurred_at' => now(),
        ]);

        $lineas = AuditoriaPresentacion::detalleLineas($auditoria);

        $this->assertSame('Estado cambiado', AuditoriaPresentacion::accionLabel('estado_cambiado'));
        $this->assertTrue(collect($lineas)->contains(fn (array $linea): bool => $linea['label'] === 'Motivo' && $linea['value'] === 'Fin de ciclo'));
        $this->assertTrue(collect($lineas)->contains(fn (array $linea): bool => $linea['label'] === 'Estado anterior'));
    }
}
