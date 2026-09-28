<?php

namespace App\Actions\Empresa;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\AuditoriaCategoria;
use App\Models\Empresa;
use App\Models\User;
use App\Services\EmpresaLogoStorageService;
use App\Support\EmpresaConfiguracion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateEmpresaConfiguracionAction
{
    public function __construct(
        private EmpresaLogoStorageService $logoStorage,
        private RegistrarAuditoriaAction $auditoria,
    ) {}

    /**
     * @param  array{
     *     nombre: string,
     *     zona_horaria: string,
     *     huevos_por_maple: int|string,
     *     maples_por_cajon: int|string,
     *     quitar_logo?: bool,
     *     logo?: UploadedFile|null
     * }  $data
     */
    public function execute(User $actor, Empresa $empresa, array $data): Empresa
    {
        Gate::forUser($actor)->authorize('update', $empresa);

        $validated = validator($data, [
            'nombre' => ['required', 'string', 'max:120'],
            'zona_horaria' => ['required', 'string', Rule::in(EmpresaConfiguracion::zonasHorariasPermitidas())],
            'huevos_por_maple' => ['required', 'integer', 'min:1', 'max:1000'],
            'maples_por_cajon' => ['required', 'integer', 'min:1', 'max:100'],
            'quitar_logo' => ['sometimes', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ], [
            'zona_horaria.in' => 'Seleccioná una zona horaria válida.',
            'logo.image' => 'El logo debe ser una imagen.',
            'logo.max' => 'El logo no puede superar 2 MB.',
        ])->validate();

        $configuracion = $empresa->configuracion ?? [];
        $antes = [
            'nombre' => $empresa->nombre,
            'zona_horaria' => $configuracion['zona_horaria'] ?? null,
            'huevos_por_maple' => $configuracion['huevos_por_maple'] ?? null,
            'maples_por_cajon' => $configuracion['maples_por_cajon'] ?? null,
            'logo_path' => $empresa->logo_path,
        ];
        $operativa = new EmpresaConfiguracion(
            zonaHoraria: $validated['zona_horaria'],
            huevosPorMaple: (int) $validated['huevos_por_maple'],
            maplesPorCajon: (int) $validated['maples_por_cajon'],
        );

        $configuracion = array_merge($configuracion, $operativa->toConfiguracionArray());

        $logoPath = $empresa->logo_path;

        if (($validated['quitar_logo'] ?? false) === true) {
            $this->logoStorage->deleteIfStored($logoPath);
            $logoPath = null;
        }

        if (($data['logo'] ?? null) instanceof UploadedFile) {
            $this->logoStorage->deleteIfStored($logoPath);
            $logoPath = $this->logoStorage->store($empresa, $data['logo']);
        }

        DB::transaction(function () use ($actor, $empresa, $validated, $configuracion, $logoPath, $antes): void {
            $empresa->update([
                'nombre' => trim($validated['nombre']),
                'logo_path' => $logoPath,
                'configuracion' => $configuracion,
            ]);

            $this->auditoria->execute(
                $actor,
                AuditoriaCategoria::Empresa,
                'configuracion_actualizada',
                Empresa::class,
                $empresa->id,
                $empresa->id,
                metadata: [
                    'antes' => $antes,
                    'despues' => [
                        'nombre' => trim($validated['nombre']),
                        'zona_horaria' => $validated['zona_horaria'],
                        'huevos_por_maple' => (int) $validated['huevos_por_maple'],
                        'maples_por_cajon' => (int) $validated['maples_por_cajon'],
                        'logo_path' => $logoPath,
                    ],
                ],
            );
        });

        return $empresa->fresh();
    }
}
