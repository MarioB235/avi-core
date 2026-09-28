<?php

namespace App\Actions\Auditoria;

use App\Enums\AuditoriaCategoria;
use App\Exceptions\AuditoriaCriticaException;
use App\Models\Auditoria;
use App\Models\User;
use App\Support\AuditoriaMetadataSanitizer;
use Illuminate\Support\Carbon;
use Throwable;

class RegistrarAuditoriaAction
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        User $actor,
        AuditoriaCategoria $categoria,
        string $accion,
        ?string $entidadTipo = null,
        ?int $entidadId = null,
        ?int $empresaId = null,
        ?string $motivo = null,
        array $metadata = [],
        ?Carbon $occurredAt = null,
    ): Auditoria {
        $empresaId = $empresaId ?? $actor->empresa_id;

        try {
            return Auditoria::query()->create([
                'empresa_id' => $empresaId,
                'actor_id' => $actor->id,
                'categoria' => $categoria,
                'accion' => $accion,
                'entidad_tipo' => $entidadTipo,
                'entidad_id' => $entidadId,
                'motivo' => $motivo !== null && trim($motivo) !== '' ? trim($motivo) : null,
                'metadata' => AuditoriaMetadataSanitizer::sanitize($metadata),
                'occurred_at' => $occurredAt ?? now(),
            ]);
        } catch (Throwable $exception) {
            throw new AuditoriaCriticaException(previous: $exception);
        }
    }
}
