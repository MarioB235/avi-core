<?php

namespace App\Actions\Documento;

use App\Enums\DocumentoEmitidoFormato;
use App\Models\DocumentoEmitido;
use App\Models\User;
use App\Support\PoliticaRetencionD07;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrarDocumentoEmitidoAction
{
    public function __construct(private PoliticaRetencionD07 $politicaRetencion) {}

    /**
     * @param  array<string, mixed>  $filtros
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        User $actor,
        string $tipo,
        DocumentoEmitidoFormato $formato,
        string $contenido,
        string $nombreArchivo,
        ?Carbon $fechaCorte = null,
        array $filtros = [],
        array $metadata = [],
        ?Carbon $emitidoAt = null,
    ): DocumentoEmitido {
        $empresaId = $actor->empresa_id;

        if ($empresaId === null) {
            throw ValidationException::withMessages([
                'empresa_id' => 'Solo usuarios de empresa pueden emitir documentos.',
            ]);
        }

        $tipo = trim($tipo);
        $nombreArchivo = trim($nombreArchivo);

        if ($tipo === '' || $nombreArchivo === '') {
            throw ValidationException::withMessages([
                'tipo' => 'El tipo y el nombre del archivo son obligatorios.',
            ]);
        }

        if ($contenido === '') {
            throw ValidationException::withMessages([
                'contenido' => 'El documento emitido no puede estar vacío.',
            ]);
        }

        $disk = $this->politicaRetencion->documentosStorageDisk();
        $emitidoAt ??= now();
        $checksum = hash('sha256', $contenido);
        $empresaCodigo = Str::slug((string) ($actor->empresa?->codigo ?? 'empresa-'.$empresaId), '_');
        $safeNombre = Str::slug(pathinfo($nombreArchivo, PATHINFO_FILENAME), '_');
        $extension = $formato->extension();
        $relativePath = sprintf(
            'empresas/%s/documentos-emitidos/%s/%s/%s_%s.%s',
            $empresaCodigo,
            $tipo,
            $emitidoAt->format('Y/m'),
            $emitidoAt->format('YmdHis'),
            $safeNombre !== '' ? $safeNombre : 'documento',
            $extension,
        );

        if (Storage::disk($disk)->exists($relativePath)) {
            throw ValidationException::withMessages([
                'storage_path' => 'Ya existe un documento emitido con la misma ruta de almacenamiento.',
            ]);
        }

        Storage::disk($disk)->put($relativePath, $contenido);

        return DocumentoEmitido::query()->create([
            'empresa_id' => $empresaId,
            'emitido_por' => $actor->id,
            'tipo' => $tipo,
            'formato' => $formato,
            'nombre_archivo' => $nombreArchivo,
            'storage_disk' => $disk,
            'storage_path' => $relativePath,
            'checksum_sha256' => $checksum,
            'filtros' => $filtros,
            'metadata' => $metadata,
            'emitido_at' => $emitidoAt,
            'fecha_corte' => $fechaCorte,
        ]);
    }
}
