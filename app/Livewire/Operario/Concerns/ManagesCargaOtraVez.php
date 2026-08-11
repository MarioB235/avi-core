<?php

namespace App\Livewire\Operario\Concerns;

trait ManagesCargaOtraVez
{
    protected function trasGuardarConOtraVez(string $estadoProperty, callable $resetForm, string $mensajeSnackbar): void
    {
        $resetForm();
        $this->{$estadoProperty} = true;
        $this->dispatch('snackbar-show', message: $mensajeSnackbar, variant: 'success');
    }

    protected function prepararOtraCarga(string $estadoProperty, callable $resetForm): void
    {
        $this->{$estadoProperty} = false;
        $resetForm();
    }

    protected function cerrarDialogoCarga(string $dialogProperty, string $estadoProperty, callable $resetForm): void
    {
        $this->{$dialogProperty} = false;
        $this->{$estadoProperty} = false;
        $resetForm();
    }
}
