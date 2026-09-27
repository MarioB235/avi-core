<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Services\OperarioGalponService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ManagesMuertesForm
{
    use ManagesCargaGuardada;

    public bool $dialogMuertesAbierto = false;

    public string $muertes = '';

    public string $muertesIdempotenciaClave = '';

    public function abrirFormularioMuertes(OperarioGalponService $operarioGalponService): void
    {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioMuertes();
        $this->dialogMuertesAbierto = true;
    }

    public function updatedDialogMuertesAbierto(bool $abierto): void
    {
        if (! $abierto) {
            $this->resetFormularioMuertes();
        }
    }

    public function cerrarDialogoMuertes(): void
    {
        $this->cerrarDialogoCarga('dialogMuertesAbierto', fn () => $this->resetFormularioMuertes());
    }

    public function guardarMuertes(
        RegistrarCargaMuertesAction $registrarCargaMuertes,
        OperarioGalponService $operarioGalponService,
    ): void {
        $validated = $this->validate([
            'muertes' => ['required', 'integer', 'min:1'],
        ], [
            'muertes.required' => 'Ingresá la cantidad de muertes.',
            'muertes.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogMuertesAbierto');

        if ($galpon === null) {
            return;
        }

        try {
            $registrarCargaMuertes->execute(
                auth()->user(),
                $galpon,
                (int) $validated['muertes'],
                null,
                $this->muertesIdempotenciaClave,
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->finalizarGuardadoCarga(
            'dialogMuertesAbierto',
            fn () => $this->resetFormularioMuertes(),
            'Muertes guardadas.',
        );
    }

    private function resetFormularioMuertes(): void
    {
        $this->reset(['muertes']);
        $this->muertesIdempotenciaClave = (string) Str::uuid();
        $this->resetValidation();
    }
}
