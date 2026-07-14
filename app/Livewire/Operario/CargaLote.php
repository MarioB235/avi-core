<?php

namespace App\Livewire\Operario;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Carga de lote')]
class CargaLote extends Component
{
    public function mount(): void
    {
        $this->redirectRoute('operario.cargar', ['form' => 'lote'], navigate: true);
    }

    public function render(): View
    {
        return view('livewire._redirect-placeholder');
    }
}
