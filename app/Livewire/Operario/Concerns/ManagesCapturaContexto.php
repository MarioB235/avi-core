<?php

namespace App\Livewire\Operario\Concerns;

use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;

trait ManagesCapturaContexto
{
    public ?int $capturaContextoGalponId = null;

    public ?string $capturaContextoRol = null;

    protected function registrarContextoCapturaAlAbrirDialogo(): void
    {
        $user = auth()->user();

        $this->capturaContextoGalponId = $user?->ultimo_galpon_id !== null
            ? (int) $user->ultimo_galpon_id
            : null;
        $this->capturaContextoRol = $user?->rol->value;
    }

    protected function limpiarContextoCaptura(): void
    {
        $this->capturaContextoGalponId = null;
        $this->capturaContextoRol = null;
    }

    protected function tieneDialogoCapturaAbierto(): bool
    {
        return $this->dialogHuevosAbierto
            || $this->dialogMuertesAbierto
            || $this->dialogDescarteAbierto
            || $this->dialogAlimentoAbierto
            || $this->dialogVacunacionAbierto
            || $this->dialogLoteAbierto;
    }

    protected function capturaContextoEsObsoleto(): bool
    {
        if (! $this->tieneDialogoCapturaAbierto()) {
            return false;
        }

        $user = auth()->user()?->fresh();

        if ($user === null) {
            return true;
        }

        if ($this->capturaContextoGalponId !== null
            && (int) ($user->ultimo_galpon_id ?? 0) !== $this->capturaContextoGalponId) {
            return true;
        }

        if ($this->capturaContextoRol !== null && $user->rol->value !== $this->capturaContextoRol) {
            return true;
        }

        if ($this->dialogLoteAbierto && ! $user->rol->canCreateLote()) {
            return true;
        }

        return false;
    }

    protected function invalidarFormulariosCapturaObsoletos(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
        string $mensaje,
    ): void {
        if (! $this->tieneDialogoCapturaAbierto()) {
            return;
        }

        if ($this->dialogHuevosAbierto) {
            $this->cerrarDialogoCarga('dialogHuevosAbierto', fn () => $this->resetFormularioHuevos());
        }

        if ($this->dialogMuertesAbierto) {
            $this->cerrarDialogoCarga('dialogMuertesAbierto', fn () => $this->resetFormularioMuertes());
        }

        if ($this->dialogDescarteAbierto) {
            $this->cerrarDialogoCarga('dialogDescarteAbierto', fn () => $this->resetFormularioDescarte());
        }

        if ($this->dialogAlimentoAbierto) {
            $this->cerrarDialogoCarga('dialogAlimentoAbierto', fn () => $this->resetFormularioAlimento());
        }

        if ($this->dialogVacunacionAbierto) {
            $this->cerrarDialogoCarga(
                'dialogVacunacionAbierto',
                fn () => $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService),
            );
        }

        if ($this->dialogLoteAbierto) {
            $this->dialogLoteAbierto = false;
            $this->resetFormularioLote($operarioGalponService);
        }

        $this->limpiarContextoCaptura();

        if ($this->galponId === null) {
            $this->selectorGalponAbierto = true;
        }

        $this->dispatch('snackbar-show', message: $mensaje, variant: 'warning');
    }

    protected function revalidarCapturaObsoletaEnHydrate(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
        ?int $galponIdAnterior,
    ): void {
        if (! $this->tieneDialogoCapturaAbierto()) {
            return;
        }

        $galponCambio = $galponIdAnterior !== $this->galponId;

        if ($galponCambio || $this->capturaContextoEsObsoleto()) {
            $mensaje = match (true) {
                $galponCambio && $this->galponId === null => 'El galpón ya no está disponible: reiniciamos el formulario abierto.',
                $galponCambio => 'Cambió el galpón: reiniciamos el formulario abierto.',
                default => 'Tu acceso cambió: reiniciamos el formulario abierto.',
            };

            $this->invalidarFormulariosCapturaObsoletos(
                $operarioGalponService,
                $operarioGalponResumenService,
                $mensaje,
            );
        }
    }

    protected function abortarSiCapturaObsoleta(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): bool {
        if (! $this->capturaContextoEsObsoleto()) {
            return false;
        }

        $this->invalidarFormulariosCapturaObsoletos(
            $operarioGalponService,
            $operarioGalponResumenService,
            'El contexto cambió: reiniciamos el formulario antes de guardar.',
        );

        return true;
    }
}
