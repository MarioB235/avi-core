<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Services\OperarioGalponService;

trait ManagesHuevosForm
{
    use ManagesCargaOtraVez;

    public bool $dialogHuevosAbierto = false;

    public bool $huevosRecienGuardados = false;

    public string $huevos = '';

    public string $huevosDescarte = '0';

    public function abrirFormularioHuevos(OperarioGalponService $operarioGalponService): void
    {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->huevosRecienGuardados = false;
        $this->resetFormularioHuevos();
        $this->dialogHuevosAbierto = true;
    }

    public function updatedDialogHuevosAbierto(bool $abierto): void
    {
        if (! $abierto) {
            $this->huevosRecienGuardados = false;
            $this->resetFormularioHuevos();
        }
    }

    public function cargarOtraVezHuevos(): void
    {
        $this->prepararOtraCarga('huevosRecienGuardados', fn () => $this->resetFormularioHuevos());
    }

    public function cerrarDialogoHuevos(): void
    {
        $this->cerrarDialogoCarga('dialogHuevosAbierto', 'huevosRecienGuardados', fn () => $this->resetFormularioHuevos());
    }

    public function guardarHuevos(
        RegistrarCargaHuevosAction $registrarCargaHuevos,
        OperarioGalponService $operarioGalponService,
    ): void {
        $validated = $this->validate([
            'huevos' => ['required', 'integer', 'min:0'],
            'huevosDescarte' => ['required', 'integer', 'min:0'],
        ], [
            'huevos.required' => 'Ingresá los huevos aptos.',
            'huevos.min' => 'Los huevos aptos no pueden ser negativos.',
            'huevosDescarte.required' => 'Ingresá los huevos de descarte (0 si no hubo).',
            'huevosDescarte.min' => 'El descarte no puede ser negativo.',
        ]);

        $huevosAptos = (int) $validated['huevos'];
        $huevosDescarte = (int) $validated['huevosDescarte'];

        if ($huevosAptos + $huevosDescarte < 1) {
            $this->addError('huevos', 'Ingresá al menos un huevo apto o de descarte.');

            return;
        }

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogHuevosAbierto');

        if ($galpon === null) {
            return;
        }

        $registrarCargaHuevos->execute(
            auth()->user(),
            $galpon,
            $huevosAptos,
            $huevosDescarte,
            null,
        );

        $this->trasGuardarConOtraVez(
            'huevosRecienGuardados',
            fn () => $this->resetFormularioHuevos(),
            'Huevos guardados.',
        );
    }

    private function resetFormularioHuevos(): void
    {
        $this->reset(['huevos', 'huevosDescarte']);
        $this->huevosDescarte = '0';
        $this->resetValidation();
    }
}
