<?php

namespace App\Models\Concerns;

use Illuminate\Validation\ValidationException;

trait PreventsHardDelete
{
    protected static function bootPreventsHardDelete(): void
    {
        static::deleting(function (): void {
            throw ValidationException::withMessages([
                'id' => static::hardDeleteRejectionMessage(),
            ]);
        });
    }

    protected static function hardDeleteRejectionMessage(): string
    {
        return 'No se puede eliminar: inactivá el registro para conservar la trazabilidad.';
    }
}
