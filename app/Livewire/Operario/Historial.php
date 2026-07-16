<?php

namespace App\Livewire\Operario;

use App\Livewire\Operario\Concerns\ManagesGalponSelector;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.operario-mobile')]
#[Title('Historial')]
class Historial extends Component
{
    use ManagesGalponSelector;
    use WithPagination;

    #[Url(as: 'fecha', history: true)]
    public ?string $fecha = null;

    public function mount(OperarioGalponService $operarioGalponService): void
    {
        $this->bootGalponSelector($operarioGalponService);
        $this->validarFecha();
    }

    public function updatedFecha(): void
    {
        $this->validarFecha();
        $this->resetPage();
    }

    public function verTodasLasFechas(): void
    {
        $this->fecha = null;
        $this->resetPage();
    }

    public function render(OperarioGalponService $operarioGalponService): View
    {
        $user = auth()->user();

        $galpon = $user ? $operarioGalponService->galponActual($user) : null;

        $registros = $operarioGalponService
            ->historialPaginado($user, $this->fecha, 20, $this->getPage());

        return view('livewire.operario.historial', [
            'galpon' => $galpon,
            'galpones' => $operarioGalponService->galponesDisponibles($user),
            'registros' => $registros,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
            'fechaError' => $this->getErrorBag()->first('fecha'),
        ]);
    }

    private function validarFecha(): void
    {
        if ($this->fecha === null || $this->fecha === '') {
            $this->fecha = null;
            $this->resetErrorBag('fecha');

            return;
        }

        $validator = validator(
            ['fecha' => $this->fecha],
            ['fecha' => ['required', 'date', 'before_or_equal:today']],
            [
                'fecha.date' => 'La fecha seleccionada no es válida.',
                'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            ],
        );

        if ($validator->fails()) {
            $this->fecha = null;
            $this->resetErrorBag('fecha');
            $this->addError('fecha', (string) $validator->errors()->first('fecha'));

            return;
        }

        $this->resetErrorBag('fecha');
    }
}
