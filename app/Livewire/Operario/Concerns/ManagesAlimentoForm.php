<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaAlimentoAction;
use App\Services\OperarioGalponService;
use App\Support\AlimentoValidacion;
use App\Support\IdempotenciaCaptura;
use Illuminate\Validation\ValidationException;

trait ManagesAlimentoForm
{
    use ManagesCargaGuardada;

    public bool $dialogAlimentoAbierto = false;

    public string $alimentoKg = '';

    public string $alimentoIdempotenciaClave = '';

    public function abrirFormularioAlimento(OperarioGalponService $operarioGalponService): void
    {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioAlimento();
        $this->dialogAlimentoAbierto = true;
        $this->registrarContextoCapturaAlAbrirDialogo();
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
        $kg = AlimentoValidacion::parseKg($this->alimentoKg);

        if ($kg === null) {
            $this->addError('alimentoKg', 'Ingresá los kilos con números (podés usar coma decimal).');

            return;
        }

        try {
            AlimentoValidacion::assertRango($kg);
        } catch (ValidationException $exception) {
            $this->mapearValidationExceptionDeCarga($exception);

            return;
        }

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogAlimentoAbierto');

        if ($galpon === null) {
            return;
        }

        $this->ejecutarEnvioCarga(
            fn () => $registrarCargaAlimento->execute(
                auth()->user(),
                $galpon,
                $kg,
                null,
                $this->alimentoIdempotenciaClave,
            ),
            'dialogAlimentoAbierto',
            fn () => $this->resetFormularioAlimento(),
            'Entrega de alimento guardada.',
        );
    }

    private function resetFormularioAlimento(): void
    {
        $this->reset(['alimentoKg']);
        $this->alimentoIdempotenciaClave = IdempotenciaCaptura::generarClave();
        $this->limpiarEstadoEnvioCarga();
        $this->resetValidation();
    }
}
