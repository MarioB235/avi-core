<?php

namespace App\Livewire\Operario;

use App\Enums\TipoHuevo;
use App\Enums\VacunaTipo;
use App\Livewire\Operario\Concerns\ManagesHuevosForm;
use App\Livewire\Operario\Concerns\ManagesLoteForm;
use App\Livewire\Operario\Concerns\ManagesMuertesForm;
use App\Livewire\Operario\Concerns\ManagesVacunacionForm;
use App\Models\Galpon;
use App\Models\Lote;
use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Cargar')]
class CargarHub extends Component
{
    use ManagesHuevosForm;
    use ManagesLoteForm;
    use ManagesMuertesForm;
    use ManagesVacunacionForm;

    public function mount(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        $form = request()->query('form');

        if (! in_array($form, ['huevos', 'muertes', 'vacunacion', 'lote'], true)) {
            return;
        }

        if ($form === 'lote') {
            if (! auth()->user()->rol->canCreateLote()) {
                return;
            }

            $this->resetFormularioLote($operarioGalponService);
            $this->dialogLoteAbierto = true;

            return;
        }

        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        if ($form === 'huevos') {
            $this->resetFormularioHuevos();
            $this->dialogHuevosAbierto = true;

            return;
        }

        if ($form === 'muertes') {
            $this->resetFormularioMuertes();
            $this->dialogMuertesAbierto = true;

            return;
        }

        $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService);
        $this->dialogVacunacionAbierto = true;
    }

    public function render(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): View {
        $user = auth()->user();
        $galpon = $operarioGalponService->galponActual($user);

        /** @var Collection<int, Lote> $lotesActivos */
        $lotesActivos = $galpon !== null
            ? $operarioGalponResumenService->lotesActivos($galpon)
            : new Collection;

        return view('livewire.operario.cargar-hub', [
            'galpon' => $galpon,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
            'lotesActivos' => $lotesActivos,
            'vacunas' => VacunaTipo::options(),
            'puedeRegistrarLote' => auth()->user()->rol->canCreateLote(),
            'galponesDisponibles' => $operarioGalponService->galponesDisponibles(auth()->user()),
            'tiposHuevoUi' => TipoHuevo::optionsUi(),
        ]);
    }

    protected function ensureGalponSeleccionado(OperarioGalponService $operarioGalponService): bool
    {
        if ($operarioGalponService->galponActual(auth()->user()) !== null) {
            return true;
        }

        $this->redirectToHomeConSelectorGalpon();

        return false;
    }

    protected function resolveGalponParaGuardar(
        OperarioGalponService $operarioGalponService,
        string $dialogProperty,
    ): ?Galpon {
        $galpon = $operarioGalponService->galponActual(auth()->user());

        if ($galpon !== null) {
            return $galpon;
        }

        $this->{$dialogProperty} = false;
        $this->redirectToHomeConSelectorGalpon();

        return null;
    }

    private function redirectToHomeConSelectorGalpon(): void
    {
        session()->flash('abrirSelectorGalpon', true);
        $this->redirectRoute('operario.home', navigate: true);
    }
}
