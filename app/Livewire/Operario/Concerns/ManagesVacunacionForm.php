<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarVacunacionAction;
use App\Enums\VacunaTipo;
use App\Models\Lote;
use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;
use Illuminate\Validation\Rule;

trait ManagesVacunacionForm
{
    public bool $dialogVacunacionAbierto = false;

    public string $loteId = '';

    public string $vacuna = '';

    public function abrirFormularioVacunacion(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService);
        $this->dialogVacunacionAbierto = true;
    }

    public function updatedDialogVacunacionAbierto(
        bool $abierto,
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        if (! $abierto) {
            $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService);
        }
    }

    public function guardarVacunacion(
        RegistrarVacunacionAction $registrarVacunacion,
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        $validated = $this->validate([
            'loteId' => ['required', 'integer', 'min:1'],
            'vacuna' => ['required', Rule::enum(VacunaTipo::class)],
        ], [
            'loteId.required' => 'Elegí el lote a vacunar.',
            'vacuna.required' => 'Elegí la vacuna aplicada.',
        ]);

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogVacunacionAbierto');

        if ($galpon === null) {
            return;
        }

        $lote = Lote::query()
            ->forEmpresa((int) auth()->user()->empresa_id)
            ->whereKey((int) $validated['loteId'])
            ->first();

        if ($lote === null) {
            $this->addError('loteId', 'El lote seleccionado no es válido.');

            return;
        }

        $registrarVacunacion->execute(
            auth()->user(),
            $galpon,
            $lote,
            VacunaTipo::from($validated['vacuna']),
            null,
        );

        $this->dialogVacunacionAbierto = false;
        $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService);
        $this->dispatch('snackbar-show', message: 'Vacunación guardada.', variant: 'success');
    }

    private function resetFormularioVacunacion(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        $galpon = $operarioGalponService->galponActual(auth()->user());
        $loteId = '';

        if ($galpon !== null) {
            $lotes = $operarioGalponResumenService->lotesActivos($galpon);

            if ($lotes->count() === 1) {
                $loteId = (string) $lotes->first()->id;
            }
        }

        $this->reset(['vacuna']);
        $this->loteId = $loteId;
        $this->resetValidation();
    }
}
