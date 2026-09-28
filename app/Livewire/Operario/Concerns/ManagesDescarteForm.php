<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaDescarteAction;
use App\Services\OperarioGalponService;
use App\Support\IdempotenciaCaptura;

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
        $this->registrarContextoCapturaAlAbrirDialogo();
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

        $this->ejecutarEnvioCarga(
            fn () => $registrarCargaDescarte->execute(
                auth()->user(),
                $galpon,
                (int) $validated['descarteAves'],
                null,
                $this->descarteIdempotenciaClave,
            ),
            'dialogDescarteAbierto',
            fn () => $this->resetFormularioDescarte(),
            'Descarte de aves guardado.',
        );
    }

    public function confirmarCeroDescarte(
        RegistrarCargaDescarteAction $registrarCargaDescarte,
        OperarioGalponService $operarioGalponService,
    ): void {
        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogDescarteAbierto');

        if ($galpon === null) {
            return;
        }

        $this->ejecutarEnvioCarga(
            fn () => $registrarCargaDescarte->execute(
                auth()->user(),
                $galpon,
                0,
                null,
                $this->descarteIdempotenciaClave,
                true,
            ),
            'dialogDescarteAbierto',
            fn () => $this->resetFormularioDescarte(),
            'Cero confirmado.',
        );
    }

    private function resetFormularioDescarte(): void
    {
        $this->reset(['descarteAves']);
        $this->descarteIdempotenciaClave = IdempotenciaCaptura::generarClave();
        $this->limpiarEstadoEnvioCarga();
        $this->resetValidation();
    }
}
