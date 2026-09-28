<?php

namespace App\Support;

use App\Enums\RetencionD07Categoria;
use Illuminate\Support\Carbon;

class PoliticaRetencionD07
{
    public function notaAcordada(): string
    {
        return (string) config('avicore.retencion.d07.nota', '');
    }

    public function purgeHabilitado(): bool
    {
        return (bool) config('avicore.retencion.d07.purge_habilitado', false);
    }

    public function plazoMeses(RetencionD07Categoria $categoria): int
    {
        $key = match ($categoria) {
            RetencionD07Categoria::Operativa => 'operativa_meses',
            RetencionD07Categoria::Auditoria => 'auditoria_meses',
            RetencionD07Categoria::Correcciones => 'correcciones_meses',
            RetencionD07Categoria::DocumentosEmitidos => 'documentos_emitidos_meses',
            RetencionD07Categoria::Usuarios => 'usuarios_meses',
        };

        return max(1, (int) config("avicore.retencion.d07.{$key}", 60));
    }

    public function fechaLimiteRetencion(RetencionD07Categoria $categoria, ?Carbon $referencia = null): Carbon
    {
        $referencia ??= now();

        return $referencia->copy()->subMonths($this->plazoMeses($categoria))->startOfDay();
    }

    public function documentosStorageDisk(): string
    {
        return (string) config('avicore.retencion.d07.documentos_disk', 'local');
    }

    /**
     * @return array<string, int|string|bool>
     */
    public function resumenOperativo(): array
    {
        return [
            'nota' => $this->notaAcordada(),
            'purge_habilitado' => $this->purgeHabilitado(),
            'operativa_meses' => $this->plazoMeses(RetencionD07Categoria::Operativa),
            'auditoria_meses' => $this->plazoMeses(RetencionD07Categoria::Auditoria),
            'correcciones_meses' => $this->plazoMeses(RetencionD07Categoria::Correcciones),
            'documentos_emitidos_meses' => $this->plazoMeses(RetencionD07Categoria::DocumentosEmitidos),
            'usuarios_meses' => $this->plazoMeses(RetencionD07Categoria::Usuarios),
            'documentos_disk' => $this->documentosStorageDisk(),
        ];
    }
}
