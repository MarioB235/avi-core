<?php

namespace App\Support;

use App\Models\Auditoria;
use Illuminate\Support\Str;

class AuditoriaPresentacion
{
    /**
     * @return list<array{label: string, value: string}>
     */
    public static function detalleLineas(Auditoria $auditoria): array
    {
        $lineas = [
            ['label' => 'Fecha y hora', 'value' => $auditoria->occurred_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—'],
            ['label' => 'Actor', 'value' => $auditoria->actor?->name ?? '—'],
            ['label' => 'Categoría', 'value' => $auditoria->categoria->label()],
            ['label' => 'Acción', 'value' => self::accionLabel($auditoria->accion)],
        ];

        if ($auditoria->entidad_tipo !== null) {
            $lineas[] = ['label' => 'Entidad', 'value' => $auditoria->entidad_tipo.($auditoria->entidad_id !== null ? ' #'.$auditoria->entidad_id : '')];
        }

        if ($auditoria->motivo !== null && trim($auditoria->motivo) !== '') {
            $lineas[] = ['label' => 'Motivo', 'value' => $auditoria->motivo];
        }

        foreach ($auditoria->metadata ?? [] as $clave => $valor) {
            $lineas[] = [
                'label' => self::metadataLabel((string) $clave),
                'value' => self::metadataValor($valor),
            ];
        }

        return $lineas;
    }

    public static function resumenTitulo(Auditoria $auditoria): string
    {
        return self::accionLabel($auditoria->accion);
    }

    public static function resumenSubtitulo(Auditoria $auditoria): string
    {
        $actor = $auditoria->actor?->name ?? 'Sistema';
        $fecha = $auditoria->occurred_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') ?? '—';

        return "{$actor} · {$fecha}";
    }

    public static function accionLabel(string $accion): string
    {
        return match ($accion) {
            'creado' => 'Usuario creado',
            'actualizado' => 'Usuario actualizado',
            'password_reseteado' => 'Contraseña reseteada',
            'estado_cambiado' => 'Estado cambiado',
            'configuracion_actualizada' => 'Configuración actualizada',
            'soporte_iniciado' => 'Soporte iniciado',
            'soporte_finalizado' => 'Soporte finalizado',
            'registrado' => 'Lote registrado',
            'anulado' => 'Registro anulado',
            'corregido' => 'Registro corregido',
            default => Str::headline(str_replace('_', ' ', $accion)),
        };
    }

    private static function metadataLabel(string $clave): string
    {
        return match ($clave) {
            'estado_anterior', 'estado_previo' => 'Estado anterior',
            'estado_nuevo', 'estado_post' => 'Estado nuevo',
            'documento' => 'Documento',
            'rol' => 'Rol',
            default => Str::headline(str_replace('_', ' ', $clave)),
        };
    }

    private static function metadataValor(mixed $valor): string
    {
        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        if (is_array($valor)) {
            return json_encode($valor, JSON_UNESCAPED_UNICODE) ?: '—';
        }

        if ($valor === null || $valor === '') {
            return '—';
        }

        return (string) $valor;
    }
}
