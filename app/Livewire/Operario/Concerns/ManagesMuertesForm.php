<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Services\OperarioGalponService;
use App\Support\IdempotenciaCaptura;

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
        $this->registrarContextoCapturaAlAbrirDialogo();
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

        $this->ejecutarEnvioCarga(
            fn () => $registrarCargaMuertes->execute(
                auth()->user(),
                $galpon,
                (int) $validated['muertes'],
                null,
                $this->muertesIdempotenciaClave,
            ),
            'dialogMuertesAbierto',
            fn () => $this->resetFormularioMuertes(),
            'Muertes guardadas.',
        );
    }

    public function confirmarCeroMuertes(
        RegistrarCargaMuertesAction $registrarCargaMuertes,
        OperarioGalponService $operarioGalponService,
    ): void {
        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogMuertesAbierto');

        if ($galpon === null) {
            return;
        }

        $this->ejecutarEnvioCarga(
            fn () => $registrarCargaMuertes->execute(
                auth()->user(),
                $galpon,
                0,
                null,
                $this->muertesIdempotenciaClave,
                true,
            ),
            'dialogMuertesAbierto',
            fn () => $this->resetFormularioMuertes(),
            'Cero confirmado.',
        );
    }

    private function resetFormularioMuertes(): void
    {
        $this->reset(['muertes']);
        $this->muertesIdempotenciaClave = IdempotenciaCaptura::generarClave();
        $this->limpiarEstadoEnvioCarga();
        $this->resetValidation();
    }
}
