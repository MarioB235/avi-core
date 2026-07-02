<?php

namespace App\Livewire\Operario;

use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Models\Galpon;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Cargar')]
class CargarHub extends Component
{
    public bool $dialogHuevosAbierto = false;

    public bool $dialogMuertesAbierto = false;

    public string $huevos = '';

    public string $muertes = '';

    public function mount(OperarioGalponService $operarioGalponService): void
    {
        $form = request()->query('form');

        if (! in_array($form, ['huevos', 'muertes'], true)) {
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

        $this->resetFormularioMuertes();
        $this->dialogMuertesAbierto = true;
    }

    public function abrirFormularioHuevos(OperarioGalponService $operarioGalponService): void
    {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioHuevos();
        $this->dialogHuevosAbierto = true;
    }

    public function updatedDialogHuevosAbierto(bool $abierto): void
    {
        if (! $abierto) {
            $this->resetFormularioHuevos();
        }
    }

    public function abrirFormularioMuertes(OperarioGalponService $operarioGalponService): void
    {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioMuertes();
        $this->dialogMuertesAbierto = true;
    }

    public function updatedDialogMuertesAbierto(bool $abierto): void
    {
        if (! $abierto) {
            $this->resetFormularioMuertes();
        }
    }

    public function guardarHuevos(
        RegistrarCargaHuevosAction $registrarCargaHuevos,
        OperarioGalponService $operarioGalponService,
    ): void {
        $validated = $this->validate([
            'huevos' => ['required', 'integer', 'min:1'],
        ], [
            'huevos.required' => 'Ingresá la cantidad de huevos.',
            'huevos.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogHuevosAbierto');

        if ($galpon === null) {
            return;
        }

        $registrarCargaHuevos->execute(
            auth()->user(),
            $galpon,
            (int) $validated['huevos'],
            null,
        );

        $this->dialogHuevosAbierto = false;
        $this->resetFormularioHuevos();
        $this->dispatch('snackbar-show', message: 'Huevos guardados.', variant: 'success');
    }

    public function guardarMuertes(
        RegistrarCargaMuertesAction $registrarCargaMuertes,
        OperarioGalponService $operarioGalponService,
    ): void {
        $validated = $this->validate([
            'muertes' => ['required', 'integer', 'min:1'],
        ], [
            'muertes.required' => 'Ingresá la cantidad de muertes.',
            'muertes.min' => 'La cantidad debe ser mayor a cero.',
        ]);

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogMuertesAbierto');

        if ($galpon === null) {
            return;
        }

        $registrarCargaMuertes->execute(
            auth()->user(),
            $galpon,
            (int) $validated['muertes'],
            null,
        );

        $this->dialogMuertesAbierto = false;
        $this->resetFormularioMuertes();
        $this->dispatch('snackbar-show', message: 'Muertes guardadas.', variant: 'success');
    }

    public function render(OperarioGalponService $operarioGalponService): View
    {
        $user = auth()->user();
        $galpon = $operarioGalponService->galponActual($user);

        return view('livewire.operario.cargar-hub', [
            'galpon' => $galpon,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
        ]);
    }

    private function ensureGalponSeleccionado(OperarioGalponService $operarioGalponService): bool
    {
        if ($operarioGalponService->galponActual(auth()->user()) !== null) {
            return true;
        }

        $this->redirectToHomeConSelectorGalpon();

        return false;
    }

    private function resolveGalponParaGuardar(
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

    private function resetFormularioHuevos(): void
    {
        $this->reset(['huevos']);
        $this->resetValidation();
    }

    private function resetFormularioMuertes(): void
    {
        $this->reset(['muertes']);
        $this->resetValidation();
    }

    private function redirectToHomeConSelectorGalpon(): void
    {
        session()->flash('abrirSelectorGalpon', true);
        $this->redirectRoute('operario.home', navigate: true);
    }
}
