<?php

namespace App\Livewire\Operario;

use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Cargar')]
class CargarHub extends Component
{
    public bool $dialogHuevosAbierto = false;

    public string $huevos = '';

    public function mount(OperarioGalponService $operarioGalponService): void
    {
        if (request()->query('form') !== 'huevos') {
            return;
        }

        $user = auth()->user();
        $galpon = $operarioGalponService->galponActual($user);

        if ($galpon === null) {
            $this->redirectToHomeConSelectorGalpon();

            return;
        }

        $this->resetFormularioHuevos();
        $this->dialogHuevosAbierto = true;
    }

    public function abrirFormularioHuevos(OperarioGalponService $operarioGalponService): void
    {
        if ($operarioGalponService->galponActual(auth()->user()) === null) {
            $this->redirectToHomeConSelectorGalpon();

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

        $galpon = $operarioGalponService->galponActual(auth()->user());

        if ($galpon === null) {
            $this->dialogHuevosAbierto = false;
            $this->redirectToHomeConSelectorGalpon();

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
        $this->dispatch('snackbar-show', message: 'Carga de huevos guardada.', variant: 'success');
    }

    public function render(OperarioGalponService $operarioGalponService): View
    {
        $user = auth()->user();
        $galpon = $operarioGalponService->galponActual($user);

        return view('livewire.operario.cargar-hub', [
            'galpon' => $galpon,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
        ]);
    }

    private function resetFormularioHuevos(): void
    {
        $this->reset(['huevos']);
        $this->resetValidation();
    }

    private function redirectToHomeConSelectorGalpon(): void
    {
        session()->flash('abrirSelectorGalpon', true);
        $this->redirectRoute('operario.home', navigate: true);
    }
}
