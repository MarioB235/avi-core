<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Services\OperarioGalponService;

trait ManagesMuertesForm
{
    use ManagesCargaGuardada;

    public bool $dialogMuertesAbierto = false;

    public string $muertes = '';

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

        $registrarCargaMuertes->execute(
            auth()->user(),
            $galpon,
            (int) $validated['muertes'],
            null,
        );

        $this->finalizarGuardadoCarga(
            'dialogMuertesAbierto',
            fn () => $this->resetFormularioMuertes(),
            'Muertes guardadas.',
        );
    }

    private function resetFormularioMuertes(): void
    {
        $this->reset(['muertes']);
        $this->resetValidation();
    }
}
