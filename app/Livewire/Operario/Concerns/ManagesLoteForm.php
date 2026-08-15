<?php

namespace App\Livewire\Operario\Concerns;

use App\Actions\Lote\RegistrarLoteAction;
use App\Enums\TipoHuevo;
use App\Services\OperarioGalponService;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

trait ManagesLoteForm
{
    public bool $dialogLoteAbierto = false;

    public string $loteGalponId = '';

    public bool $tipoBlanco = false;

    public bool $tipoColor = false;

    public string $cantidadBlanco = '';

    public string $cantidadColor = '';

    public string $fechaNacimiento = '';

    public string $codigoSma = '';

    public function abrirFormularioLote(OperarioGalponService $operarioGalponService): void
    {
        if (! auth()->user()->rol->canCreateLote()) {
            return;
        }

        $this->resetFormularioLote($operarioGalponService);
        $this->dialogLoteAbierto = true;
    }

    public function updatedDialogLoteAbierto(bool $abierto, OperarioGalponService $operarioGalponService): void
    {
        if (! $abierto) {
            $this->resetFormularioLote($operarioGalponService);
        }
    }

    public function guardarLote(
        RegistrarLoteAction $registrarLote,
        OperarioGalponService $operarioGalponService,
    ): void {
        if (! auth()->user()->rol->canCreateLote()) {
            return;
        }

        $rules = [
            'loteGalponId' => ['required', 'integer', 'min:1'],
            'fechaNacimiento' => ['required', 'date', 'before_or_equal:today'],
            'codigoSma' => ['nullable', 'string', 'max:64', 'regex:/^[\pL\pN\-_.\/]+$/u'],
        ];

        if ($this->tipoBlanco) {
            $rules['cantidadBlanco'] = ['required', 'integer', 'min:1'];
        }

        if ($this->tipoColor) {
            $rules['cantidadColor'] = ['required', 'integer', 'min:1'];
        }

        $validated = $this->validate($rules, [
            'loteGalponId.required' => 'Elegí el galpón.',
            'fechaNacimiento.required' => 'Ingresá la fecha aproximada de nacimiento.',
            'fechaNacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'cantidadBlanco.required' => 'Ingresá la cantidad para huevo blanco.',
            'cantidadBlanco.min' => 'La cantidad debe ser mayor a cero.',
            'cantidadColor.required' => 'Ingresá la cantidad para huevo colorado.',
            'cantidadColor.min' => 'La cantidad debe ser mayor a cero.',
            'codigoSma.max' => 'El código SMA no puede superar los 64 caracteres.',
            'codigoSma.regex' => 'El código SMA solo puede tener letras, números y los símbolos - _ . /',
        ]);

        if (! $this->tipoBlanco && ! $this->tipoColor) {
            $this->addError('tiposHuevo', 'Marcá al menos un tipo de ave.');

            return;
        }

        $galpon = $operarioGalponService->galponDisponibleParaUsuario(
            auth()->user(),
            (int) $validated['loteGalponId'],
        );

        if ($galpon === null) {
            $this->addError('loteGalponId', 'El galpón seleccionado no es válido.');

            return;
        }

        $cantidadesPorTipo = [];

        if ($this->tipoBlanco) {
            $cantidadesPorTipo[TipoHuevo::Blanco->value] = (int) $validated['cantidadBlanco'];
        }

        if ($this->tipoColor) {
            $cantidadesPorTipo[TipoHuevo::Color->value] = (int) $validated['cantidadColor'];
        }

        try {
            $lotes = $registrarLote->execute(
                auth()->user(),
                $galpon,
                $cantidadesPorTipo,
                Carbon::parse($validated['fechaNacimiento']),
                codigoSma: $validated['codigoSma'] ?? null,
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->dialogLoteAbierto = false;
        $this->resetFormularioLote($operarioGalponService);

        $codigos = $lotes->pluck('codigo')->join(', ');
        $mensaje = $lotes->count() === 1
            ? "Lote {$codigos} registrado."
            : "Lotes registrados: {$codigos}.";

        $this->dispatch('snackbar-show', message: $mensaje, variant: 'success');
    }

    private function resetFormularioLote(OperarioGalponService $operarioGalponService): void
    {
        $galpon = $operarioGalponService->galponActual(auth()->user());
        $loteGalponId = '';

        if ($galpon !== null) {
            $loteGalponId = (string) $galpon->id;
        } else {
            $primerGalpon = $operarioGalponService->galponesDisponibles(auth()->user())->first();

            if ($primerGalpon !== null) {
                $loteGalponId = (string) $primerGalpon->id;
            }
        }

        $this->reset([
            'tipoBlanco',
            'tipoColor',
            'cantidadBlanco',
            'cantidadColor',
            'fechaNacimiento',
            'codigoSma',
        ]);
        $this->loteGalponId = $loteGalponId;
        $this->resetValidation();
    }
}
