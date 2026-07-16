<?php

namespace App\Livewire\Operario;

use App\Livewire\Operario\Concerns\ManagesGalponSelector;
use App\Models\Galpon;
use App\Models\Lote;
use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Operario')]
class Home extends Component
{
    use ManagesGalponSelector;

    public function mount(OperarioGalponService $operarioGalponService): void
    {
        $this->bootGalponSelector($operarioGalponService);
    }

    public function render(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): View {
        $user = auth()->user();
        $galpon = $operarioGalponService->galponActual($user);
        $hora = now()->hour;

        /** @var Collection<int, Galpon> $galpones */
        $galpones = $operarioGalponService->galponesDisponibles($user);
        $resumen = $galpon !== null ? $operarioGalponResumenService->resumen($galpon) : null;

        /** @var Collection<int, int> $edadSemanasPorLote */
        $edadSemanasPorLote = $resumen !== null
            ? $resumen['lotes']->mapWithKeys(
                fn (Lote $lote): array => [$lote->id => $operarioGalponResumenService->edadSemanas($lote)]
            )
            : collect();

        return view('livewire.operario.home', [
            'galpon' => $galpon,
            'galpones' => $galpones,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
            'resumen' => $resumen,
            'edadSemanasPorLote' => $edadSemanasPorLote,
            'saludo' => match (true) {
                $hora < 12 => 'Buenos días',
                $hora < 19 => 'Buenas tardes',
                default => 'Buenas noches',
            },
        ]);
    }
}
