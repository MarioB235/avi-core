<?php

namespace App\Livewire\Operario;

use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Carga de muertes')]
class CargaMuertes extends Component
{
    public function mount(OperarioGalponService $operarioGalponService): void
    {
        if ($operarioGalponService->galponActual(auth()->user()) === null) {
            session()->flash('abrirSelectorGalpon', true);
            $this->redirectRoute('operario.home', navigate: true);

            return;
        }

        $this->redirectRoute('operario.cargar', ['form' => 'muertes'], navigate: true);
    }

    public function render(): View
    {
        return view('livewire._redirect-placeholder');
    }
}
