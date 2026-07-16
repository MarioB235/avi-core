<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Services\OperarioGalponService;

trait ManagesHuevosForm
{
    public bool $dialogHuevosAbierto = false;

    public string $huevos = '';

    public function abrirFormularioHuevos(OperarioGalponService $operarioGalponService): void
    {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioHuevos();
        $this->dialogHuevosAbierto = true;
    }

    public function updatedDialogHuevosAbierto(bool $abierto): void
    {
        if (! $abierto) {
            $this->resetFormularioHuevos();
        }
    }

    public function guardarHuevos(
        RegistrarCargaHuevosAction $registrarCargaHuevos,
        OperarioGalponService $operarioGalponService,
    ): void {
        $validated = $this->validate([
            'huevos' => ['required', 'integer', 'min:1'],
        ], [
            'huevos.required' => 'Ingresá la cantidad de huevos.',
            'huevos.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogHuevosAbierto');

        if ($galpon === null) {
            return;
        }

        $registrarCargaHuevos->execute(
            auth()->user(),
            $galpon,
            (int) $validated['huevos'],
            null,
        );

        $this->dialogHuevosAbierto = false;
        $this->resetFormularioHuevos();
        $this->dispatch('snackbar-show', message: 'Huevos guardados.', variant: 'success');
    }

    private function resetFormularioHuevos(): void
    {
        $this->reset(['huevos']);
        $this->resetValidation();
    }
}
