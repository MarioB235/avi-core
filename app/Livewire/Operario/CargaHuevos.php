<?php

namespace App\Livewire\Operario;

use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Carga de huevos')]
class CargaHuevos extends Component
{
    public string $huevos = '';

    public string $observacion = '';

    public function mount(OperarioGalponService $operarioGalponService): void
    {
        if ($operarioGalponService->galponActual(auth()->user()) === null) {
            $this->redirectRoute('operario.galpon', navigate: true);
        }
    }

    public function guardar(
        RegistrarCargaHuevosAction $registrarCargaHuevos,
        OperarioGalponService $operarioGalponService,
    ): void {
        $validated = $this->validate([
            'huevos' => ['required', 'integer', 'min:1'],
            'observacion' => ['nullable', 'string', 'max:500'],
        ], [
            'huevos.required' => 'Ingresá la cantidad de huevos.',
            'huevos.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $galpon = $operarioGalponService->galponActual(auth()->user());

        if ($galpon === null) {
            $this->redirectRoute('operario.galpon', navigate: true);

            return;
        }

        $registrarCargaHuevos->execute(
            auth()->user(),
            $galpon,
            (int) $validated['huevos'],
            $validated['observacion'] ?? null,
        );

        session()->flash('status', 'Carga de huevos guardada.');

        $this->redirectRoute('operario.home', navigate: true);
    }

    public function render(OperarioGalponService $operarioGalponService): View
    {
        return view('livewire.operario.carga-huevos', [
            'galpon' => $operarioGalponService->galponActual(auth()->user()),
        ]);
    }
}
