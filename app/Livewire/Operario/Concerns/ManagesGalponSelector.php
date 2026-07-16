<?php

namespace App\Livewire\Operario\Concerns;

use App\Enums\GalponEstado;
use App\Services\OperarioGalponService;
use Illuminate\Validation\Rule;
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
        $this->galponId = $galponId;

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

        $galpon = $operarioGalponService->galponDisponibleParaUsuario($user, $this->galponId);

        if ($galpon === null) {
            throw ValidationException::withMessages([
                'galponId' => 'El galpón seleccionado no está disponible para carga.',
            ]);
        }

        $operarioGalponService->seleccionarGalpon($user, $galpon);

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
