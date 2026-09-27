<?php

namespace App\Support;

use App\Models\User;

/**
 * Política operativa de minimización de datos personales (EMP-08).
 * No constituye asesoramiento legal ni certificación normativa.
 */
final class DatosPersonales
{
    public static function canViewFullDocumento(User $viewer, User $subject): bool
    {
        if ($viewer->id === $subject->id) {
            return true;
        }

        if (! $viewer->rol->canManageUsers()) {
            return false;
        }

        if ($viewer->isAdminAvicore()) {
            return true;
        }

        return $viewer->empresa_id !== null
            && $viewer->empresa_id === $subject->empresa_id;
    }

    public static function documentoParaVista(User $viewer, User $subject): string
    {
        if (self::canViewFullDocumento($viewer, $subject)) {
            return $subject->documento;
        }

        return self::maskDocumento($subject->documento);
    }

    public static function maskDocumento(string $documento): string
    {
        $documento = trim($documento);

        if ($documento === '') {
            return '—';
        }

        $length = strlen($documento);

        $visibleDigits = max(1, (int) config('avicore.datos_personales.documento_visible_digitos', 3));

        if ($length <= $visibleDigits) {
            return str_repeat('•', $length);
        }

        $visible = substr($documento, -$visibleDigits);

        return str_repeat('•', $length - $visibleDigits).$visible;
    }

    /**
     * @return list<string>
     */
    public static function inventoryCategories(): array
    {
        return [
            'users',
            'sessions',
            'soporte_sesiones',
            'registros_operativos',
            'vacunaciones',
        ];
    }
}
