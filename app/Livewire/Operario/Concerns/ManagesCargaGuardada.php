<?php

namespace App\Livewire\Operario\Concerns;

trait ManagesCargaGuardada
{
    protected function finalizarGuardadoCarga(string $dialogProperty, callable $resetForm, string $mensajeSnackbar): void
    {
        $resetForm();
        $this->{$dialogProperty} = false;
        $this->dispatch('snackbar-show', message: $mensajeSnackbar, variant: 'success');
    }

    protected function cerrarDialogoCarga(string $dialogProperty, callable $resetForm): void
    {
        $this->{$dialogProperty} = false;
        $resetForm();
    }
}
