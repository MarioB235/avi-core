<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaDescarteAction;
use App\Services\OperarioGalponService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ManagesDescarteForm
{
    use ManagesCargaGuardada;

    public bool $dialogDescarteAbierto = false;

    public string $descarteAves = '';

    public string $descarteIdempotenciaClave = '';

    public function abrirFormularioDescarte(OperarioGalponService $operarioGalponService): void
    {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioDescarte();
        $this->dialogDescarteAbierto = true;
    }

    public function updatedDialogDescarteAbierto(bool $abierto): void
    {
        if (! $abierto) {
            $this->resetFormularioDescarte();
        }
    }

    public function cerrarDialogoDescarte(): void
    {
        $this->cerrarDialogoCarga('dialogDescarteAbierto', fn () => $this->resetFormularioDescarte());
    }

    public function guardarDescarte(
        RegistrarCargaDescarteAction $registrarCargaDescarte,
        OperarioGalponService $operarioGalponService,
    ): void {
        $validated = $this->validate([
            'descarteAves' => ['required', 'integer', 'min:1'],
        ], [
            'descarteAves.required' => 'Ingresá cuántas aves descartaste.',
            'descarteAves.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogDescarteAbierto');

        if ($galpon === null) {
            return;
        }

        try {
            $registrarCargaDescarte->execute(
                auth()->user(),
                $galpon,
                (int) $validated['descarteAves'],
                null,
                $this->descarteIdempotenciaClave,
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->finalizarGuardadoCarga(
            'dialogDescarteAbierto',
            fn () => $this->resetFormularioDescarte(),
            'Descarte de aves guardado.',
        );
    }

    private function resetFormularioDescarte(): void
    {
        $this->reset(['descarteAves']);
        $this->descarteIdempotenciaClave = (string) Str::uuid();
        $this->resetValidation();
    }
}
