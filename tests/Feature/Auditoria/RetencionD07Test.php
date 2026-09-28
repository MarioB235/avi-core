<?php

namespace Tests\Feature\Auditoria;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Actions\Documento\RegistrarDocumentoEmitidoAction;
use App\Enums\AuditoriaCategoria;
use App\Enums\DocumentoEmitidoFormato;
use App\Enums\UserRole;
use App\Models\Auditoria;
use App\Models\CorreccionRegistroOperativo;
use App\Models\DocumentoEmitido;
use App\Models\Empresa;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\RegistroOperativo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RetencionD07Test extends TestCase
{
    use RefreshDatabase;

    public function test_historial_y_auditoria_rechazan_borrado_fisico(): void
    {
        [$encargado, $registro] = $this->encargadoConRegistro();

        app(RegistrarAuditoriaAction::class)->execute(
            $encargado,
            AuditoriaCategoria::Operacion,
            'anulado',
            entidadTipo: 'registro_operativo',
            entidadId: $registro->id,
        );

        $auditoria = Auditoria::query()->sole();

        try {
            $registro->delete();
            $this->fail('Se esperaba ValidationException al borrar registro.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('D07', $exception->errors()['id'][0]);
        }

        try {
            $auditoria->delete();
            $this->fail('Se esperaba ValidationException al borrar auditoría.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('inmutable', $exception->errors()['id'][0]);
        }
    }

    public function test_correccion_rechaza_borrado_fisico(): void
    {
        $empresa = Empresa::factory()->create();
        $registro = RegistroOperativo::factory()->create(['empresa_id' => $empresa->id]);
        $correccion = CorreccionRegistroOperativo::query()->create([
            'empresa_id' => $empresa->id,
            'registro_operativo_id' => $registro->id,
            'valores_anteriores' => ['huevos' => 100],
            'valores_nuevos' => ['huevos' => 120],
            'motivo' => 'Ajuste de conteo',
            'corregido_por' => User::factory()->create(['empresa_id' => $empresa->id])->id,
            'fecha_efectiva' => now(),
        ]);

        try {
            $correccion->delete();
            $this->fail('Se esperaba ValidationException al borrar corrección.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('inmutables', $exception->errors()['id'][0]);
        }
    }

    public function test_registrar_documento_emitido_conserva_archivo_y_rechaza_borrado(): void
    {
        Storage::fake('local');

        $empresa = Empresa::factory()->create(['codigo' => 'DEMO']);
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);

        $documento = app(RegistrarDocumentoEmitidoAction::class)->execute(
            $encargado,
            'reporte_diario',
            DocumentoEmitidoFormato::Pdf,
            '%PDF-1.4 contenido de prueba',
            'reporte-diario.pdf',
            filtros: ['desde' => '2026-09-01', 'hasta' => '2026-09-07'],
            metadata: ['version' => 1],
        );

        $this->assertSame(hash('sha256', '%PDF-1.4 contenido de prueba'), $documento->checksum_sha256);
        Storage::disk('local')->assertExists($documento->storage_path);

        try {
            $documento->delete();
            $this->fail('Se esperaba ValidationException al borrar documento emitido.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('D07', $exception->errors()['id'][0]);
        }

        $this->assertSame(1, DocumentoEmitido::query()->count());
    }

    /**
     * @return array{0: User, 1: RegistroOperativo}
     */
    private function encargadoConRegistro(): array
    {
        $empresa = Empresa::factory()->create();
        $encargado = User::factory()->create([
            'empresa_id' => $empresa->id,
            'rol' => UserRole::Encargado,
        ]);
        $granja = Granja::factory()->create(['empresa_id' => $empresa->id]);
        $galpon = Galpon::factory()->forGranja($granja)->create();
        $registro = RegistroOperativo::factory()->forGalponAndUser($galpon, $encargado)->create();

        return [$encargado, $registro];
    }
}
