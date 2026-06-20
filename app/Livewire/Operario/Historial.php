<?php

namespace App\Livewire\Operario;

use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Historial')]
class Historial extends Component
{
    public function render(OperarioGalponService $operarioGalponService): View
    {
        $user = auth()->user();

        return view('livewire.operario.historial', [
            'ultimasCargas' => $operarioGalponService->ultimasCargasDelDia($user),
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon(
                $operarioGalponService->galponActual($user)
            ),
        ]);
    }
}
