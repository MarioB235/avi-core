<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarCargaAlimentoAction;
use App\Services\OperarioGalponService;
use App\Support\AlimentoValidacion;
use Illuminate\Support\Str;
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
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogAlimentoAbierto');

        if ($galpon === null) {
            return;
        }

        try {
            $registrarCargaAlimento->execute(
                auth()->user(),
                $galpon,
                $kg,
                null,
                $this->alimentoIdempotenciaClave,
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->finalizarGuardadoCarga(
            'dialogAlimentoAbierto',
            fn () => $this->resetFormularioAlimento(),
            'Entrega de alimento guardada.',
        );
    }

    private function resetFormularioAlimento(): void
    {
        $this->reset(['alimentoKg']);
        $this->alimentoIdempotenciaClave = (string) Str::uuid();
        $this->resetValidation();
    }
}
