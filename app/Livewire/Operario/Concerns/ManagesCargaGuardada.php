<?php

namespace App\Livewire\Operario\Concerns;

use Illuminate\Validation\ValidationException;
use Throwable;

trait ManagesCargaGuardada
{
    public const MENSAJE_ERROR_ENVIO = 'Revisá la conexión e intentá de nuevo. Tus datos siguen en pantalla y el reintento usa la misma intención para no duplicar la carga.';

    public ?string $cargaEnvioError = null;

    protected function limpiarEstadoEnvioCarga(): void
    {
        $this->cargaEnvioError = null;
    }

    protected function finalizarGuardadoCarga(string $dialogProperty, callable $resetForm, string $mensajeSnackbar): void
    {
        $this->limpiarEstadoEnvioCarga();
        $resetForm();
        $this->{$dialogProperty} = false;
        $this->dispatch('snackbar-show', message: $mensajeSnackbar, variant: 'success');
    }

    protected function cerrarDialogoCarga(string $dialogProperty, callable $resetForm): void
    {
        $this->{$dialogProperty} = false;
        $resetForm();
    }

    /**
     * @param  callable(): mixed  $persistir
     */
    protected function ejecutarEnvioCarga(
        callable $persistir,
        string $dialogProperty,
        callable $resetForm,
        string $mensajeSnackbar,
    ): void {
        $this->limpiarEstadoEnvioCarga();

        try {
            $persistir();
        } catch (ValidationException $exception) {
            $this->mapearValidationExceptionDeCarga($exception);

            return;
        } catch (Throwable $exception) {
            report($exception);
            $this->cargaEnvioError = self::MENSAJE_ERROR_ENVIO;

            return;
        }

        $this->finalizarGuardadoCarga($dialogProperty, $resetForm, $mensajeSnackbar);
    }

    protected function mapearValidationExceptionDeCarga(ValidationException $exception): void
    {
        $this->limpiarEstadoEnvioCarga();

        foreach ($exception->errors() as $field => $messages) {
            $campo = match ($field) {
                'galpon_id' => 'galponId',
                'lote_id' => 'loteId',
                'descarteAves' => 'descarteAves',
                default => $field,
            };

            $this->addError($campo, $messages[0]);
        }
    }
}
