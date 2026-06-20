<?php

namespace App\Livewire\Operario;

use App\Enums\GalponEstado;
use App\Models\Galpon;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Elegir galpón')]
class SelectorGalpon extends Component
{
    public ?int $galponId = null;

    public function mount(OperarioGalponService $operarioGalponService): void
    {
        $galpon = $operarioGalponService->galponActual(auth()->user());
        $this->galponId = $galpon?->id;
    }

    public function guardar(OperarioGalponService $operarioGalponService): void
    {
        $user = auth()->user();

        $this->validate([
            'galponId' => [
                'required',
                'integer',
                Rule::exists('galpones', 'id')->where(function ($query) use ($user) {
                    $query->where('empresa_id', $user->empresa_id)
                        ->where('activo', true)
                        ->where('estado', GalponEstado::Activo->value);
                }),
            ],
        ], [
            'galponId.required' => 'Elegí un galpón para continuar.',
            'galponId.exists' => 'El galpón seleccionado no está disponible para carga.',
        ]);

        $galpon = Galpon::query()->findOrFail($this->galponId);
        $operarioGalponService->seleccionarGalpon(auth()->user(), $galpon);

        session()->flash('status', 'Galpón actualizado.');

        $this->redirectRoute('operario.home', navigate: true);
    }

    public function render(OperarioGalponService $operarioGalponService): View
    {
        /** @var Collection<int, Galpon> $galpones */
        $galpones = $operarioGalponService->galponesDisponibles(auth()->user());

        return view('livewire.operario.selector-galpon', [
            'galpones' => $galpones,
        ]);
    }
}
