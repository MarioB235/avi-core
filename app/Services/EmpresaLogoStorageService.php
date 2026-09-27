<?php

namespace App\Services;

use App\Models\Empresa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmpresaLogoStorageService
{
    public function __construct(private EmpresaLogoPathGuard $pathGuard) {}

    public function store(Empresa $empresa, UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
        $allowed = ['png', 'jpg', 'jpeg', 'webp', 'svg'];

        if (! in_array($extension, $allowed, true)) {
            throw new \InvalidArgumentException('Formato de logo no permitido.');
        }

        $codigo = Str::slug((string) $empresa->codigo, '_');
        $filename = 'logo-'.now()->format('YmdHis').'.'.$extension;
        $relativePath = "empresas/logos/{$codigo}/{$filename}";

        $this->pathGuard->assertSafeStoredPath($relativePath);

        Storage::disk('public')->putFileAs(
            "empresas/logos/{$codigo}",
            $file,
            $filename,
        );

        return $relativePath;
    }

    public function deleteIfStored(?string $path): void
    {
        $safePath = $this->pathGuard->assertSafeStoredPath($path);

        if ($safePath === null) {
            return;
        }

        Storage::disk('public')->delete($safePath);
    }
}
