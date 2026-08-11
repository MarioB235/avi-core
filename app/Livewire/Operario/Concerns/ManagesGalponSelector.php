<?php

namespace App\Livewire\Operario\Concerns;

use App\Services\OperarioGalponService;
use Illuminate\Validation\ValidationException;

trait ManagesGalponSelector
{
    public ?int $galponId = null;

    public bool $selectorGalponAbierto = false;

    public function toggleSelectorGalpon(): void
    {
        $this->selectorGalponAbierto = ! $this->selectorGalponAbierto;
    }

    public function cerrarSelectorGalpon(): void
    {
        $this->selectorGalponAbierto = false;
    }

    public function seleccionarGalpon(int $galponId, OperarioGalponService $operarioGalponService): void
    {
        $user = auth()->user();
        $galpon = $operarioGalponService->galponDisponibleParaUsuario($user, $galponId);

        if ($galpon === null) {
            throw ValidationException::withMessages([
                'galponId' => 'El galpón seleccionado no está disponible para carga.',
            ]);
        }

        $operarioGalponService->seleccionarGalpon($user, $galpon);

        $this->galponId = $galpon->id;
        $this->selectorGalponAbierto = false;

        $this->dispatch('snackbar-show', message: 'Galpón actualizado.', variant: 'success');
    }

    protected function bootGalponSelector(OperarioGalponService $operarioGalponService): void
    {
        $galpon = $operarioGalponService->galponActual(auth()->user());
        $this->galponId = $galpon?->id;

        if (request()->boolean('abrir_galpon') || session('abrirSelectorGalpon')) {
            $this->selectorGalponAbierto = true;
            session()->forget('abrirSelectorGalpon');
        }
    }
}
