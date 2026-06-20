<?php

namespace App\Livewire\Operario;

use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Operario')]
class Home extends Component
{
    public function render(OperarioGalponService $operarioGalponService): View
    {
        $user = auth()->user();
        $galpon = $operarioGalponService->galponActual($user);

        return view('livewire.operario.home', [
            'galpon' => $galpon,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
            'ultimasCargas' => $operarioGalponService->ultimasCargasDelDia($user)->take(3),
        ]);
    }
}
