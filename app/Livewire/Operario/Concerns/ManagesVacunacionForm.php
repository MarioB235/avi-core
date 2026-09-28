<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Operacion\RegistrarVacunacionAction;
use App\Enums\VacunaTipo;
use App\Models\Lote;
use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;
use App\Support\IdempotenciaCaptura;
use App\Support\VacunacionValidacion;
use Illuminate\Validation\Rule;

trait ManagesVacunacionForm
{
    use ManagesCargaGuardada;

    public bool $dialogVacunacionAbierto = false;

    public string $loteId = '';

    public string $vacuna = '';

    public string $observacionVacunacion = '';

    public string $vacunacionIdempotenciaClave = '';

    public function abrirFormularioVacunacion(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService);
        $this->dialogVacunacionAbierto = true;
        $this->registrarContextoCapturaAlAbrirDialogo();
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

    public function cerrarDialogoVacunacion(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        $this->cerrarDialogoCarga(
            'dialogVacunacionAbierto',
            fn () => $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService),
        );
    }

    public function guardarVacunacion(
        RegistrarVacunacionAction $registrarVacunacion,
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        $validated = $this->validate([
            'loteId' => ['required', 'integer', 'min:1'],
            'vacuna' => ['required', Rule::enum(VacunaTipo::class)],
            'observacionVacunacion' => ['nullable', 'string', 'max:'.VacunacionValidacion::OBSERVACION_MAX],
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

        $this->ejecutarEnvioCarga(
            fn () => $registrarVacunacion->execute(
                auth()->user(),
                $galpon,
                $lote,
                VacunaTipo::from($validated['vacuna']),
                $validated['observacionVacunacion'] ?? null,
                $this->vacunacionIdempotenciaClave,
            ),
            'dialogVacunacionAbierto',
            fn () => $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService),
            'Vacunación guardada.',
        );
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

        $this->reset(['vacuna', 'observacionVacunacion']);
        $this->loteId = $loteId;
        $this->vacunacionIdempotenciaClave = IdempotenciaCaptura::generarClave();
        $this->limpiarEstadoEnvioCarga();
        $this->resetValidation();
    }
}
