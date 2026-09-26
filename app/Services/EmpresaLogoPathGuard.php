<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class EmpresaLogoPathGuard
{
    private const ALLOWED_PREFIX = 'empresas/logos/';

    /**
     * Valida una ruta relativa de logo persistida (sin URL absoluta ni traversal).
     */
    public function assertSafeStoredPath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', trim($path));

        if (
            str_starts_with($normalized, '/')
            || preg_match('#(^|/)\.\.(/|$)#', $normalized) === 1
            || preg_match('#^https?://#i', $normalized) === 1
        ) {
            throw new InvalidArgumentException('Ruta de logo no permitida.');
        }

        if (! str_starts_with($normalized, self::ALLOWED_PREFIX)) {
            throw new InvalidArgumentException('El logo debe almacenarse bajo empresas/logos/.');
        }

        return $normalized;
    }

    public function publicUrl(?string $path): ?string
    {
        $safePath = $this->assertSafeStoredPath($path);

        if ($safePath === null) {
            return null;
        }

        return Storage::disk('public')->url($safePath);
    }
}
