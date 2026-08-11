<?php

namespace App\Livewire\Operario;

use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Descarte de aves')]
class CargaDescarte extends Component
{
    public function mount(OperarioGalponService $operarioGalponService): void
    {
        if ($operarioGalponService->galponActual(auth()->user()) === null) {
            $this->redirectRoute('operario.cargar', ['abrir_galpon' => 1], navigate: true);

            return;
        }

        $this->redirectRoute('operario.cargar', ['form' => 'descarte'], navigate: true);
    }

    public function render(): View
    {
        return view('livewire._redirect-placeholder');
    }
}
