<?php

namespace App\Livewire\Concerns;

use Illuminate\Validation\ValidationException;

trait MapsEstructuraValidationErrors
{
    protected function mapearErroresLote(ValidationException $exception): void
    {
        foreach ($exception->errors() as $field => $messages) {
            $target = match ($field) {
                'galponId' => 'loteGalponId',
                'fechaNacimiento' => 'loteFechaNacimiento',
                'codigo_sma' => 'loteCodigoSma',
                'tiposHuevo' => 'loteTipoHuevo',
                default => str_starts_with($field, 'cantidad_') ? 'loteCantidad' : $field,
            };

            foreach ($messages as $message) {
                $this->addError($target, $message);
            }
        }
    }

    protected function mapearErroresGalpon(ValidationException $exception): void
    {
        foreach ($exception->errors() as $field => $messages) {
            $target = match ($field) {
                'granja_id' => 'galponGranjaId',
                'nombre' => 'galponNombre',
                'codigo' => 'galponCodigo',
                'capacidad' => 'galponCapacidad',
                'estado' => 'galponEstado',
                'activo' => 'galponActivo',
                'observacion' => 'galponObservacion',
                default => $field,
            };

            foreach ($messages as $message) {
                $this->addError($target, $message);
            }
        }
    }

    protected function mapearErroresGranja(ValidationException $exception): void
    {
        foreach ($exception->errors() as $field => $messages) {
            $target = match ($field) {
                'nombre' => 'granjaNombre',
                'codigo' => 'granjaCodigo',
                'dicose' => 'granjaDicose',
                'ubicacion' => 'granjaUbicacion',
                'activa' => 'granjaActiva',
                default => $field,
            };

            foreach ($messages as $message) {
                $this->addError($target, $message);
            }
        }
    }
}
