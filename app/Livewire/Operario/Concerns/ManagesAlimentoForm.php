<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaAlimentoAction;
use App\Services\OperarioGalponService;

trait ManagesAlimentoForm
{
    use ManagesCargaGuardada;

    public bool $dialogAlimentoAbierto = false;

    public string $alimentoKg = '';

    public function abrirFormularioAlimento(OperarioGalponService $operarioGalponService): void
    {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioAlimento();
        $this->dialogAlimentoAbierto = true;
    }

    public function updatedDialogAlimentoAbierto(bool $abierto): void
    {
        if (! $abierto) {
            $this->resetFormularioAlimento();
        }
    }

    public function cerrarDialogoAlimento(): void
    {
        $this->cerrarDialogoCarga('dialogAlimentoAbierto', fn () => $this->resetFormularioAlimento());
    }

    public function guardarAlimento(
        RegistrarCargaAlimentoAction $registrarCargaAlimento,
        OperarioGalponService $operarioGalponService,
    ): void {
        $validated = $this->validate([
            'alimentoKg' => ['required', 'numeric', 'min:0.01'],
        ], [
            'alimentoKg.required' => 'Ingresá los kilos entregados.',
            'alimentoKg.min' => 'Los kilos deben ser mayor a cero.',
        ]);

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogAlimentoAbierto');

        if ($galpon === null) {
            return;
        }

        $registrarCargaAlimento->execute(
            auth()->user(),
            $galpon,
            (float) $validated['alimentoKg'],
            null,
        );

        $this->finalizarGuardadoCarga(
            'dialogAlimentoAbierto',
            fn () => $this->resetFormularioAlimento(),
            'Entrega de alimento guardada.',
        );
    }

    private function resetFormularioAlimento(): void
    {
        $this->reset(['alimentoKg']);
        $this->resetValidation();
    }
}
