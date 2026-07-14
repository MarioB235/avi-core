<?php

namespace App\Livewire\Operario;

use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Operacion\RegistrarCargaHuevosAction;
use App\Actions\Operacion\RegistrarCargaMuertesAction;
use App\Actions\Operacion\RegistrarVacunacionAction;
use App\Enums\TipoHuevo;
use App\Enums\VacunaTipo;
use App\Models\Galpon;
use App\Models\Lote;
use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Cargar')]
class CargarHub extends Component
{
    public bool $dialogHuevosAbierto = false;

    public bool $dialogMuertesAbierto = false;

    public bool $dialogVacunacionAbierto = false;

    public bool $dialogLoteAbierto = false;

    public string $huevos = '';

    public string $muertes = '';

    public string $loteId = '';

    public string $vacuna = '';

    public string $loteGalponId = '';

    public bool $tipoBlanco = false;

    public bool $tipoColor = false;

    public string $cantidadBlanco = '';

    public string $cantidadColor = '';

    public string $fechaNacimiento = '';

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

    public function abrirFormularioVacunacion(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService);
        $this->dialogVacunacionAbierto = true;
    }

    public function updatedDialogVacunacionAbierto(
        bool $abierto,
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        if (! $abierto) {
            $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService);
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

    public function guardarVacunacion(
        RegistrarVacunacionAction $registrarVacunacion,
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        $validated = $this->validate([
            'loteId' => ['required', 'integer', 'min:1'],
            'vacuna' => ['required', Rule::enum(VacunaTipo::class)],
        ], [
            'loteId.required' => 'Elegí el lote a vacunar.',
            'vacuna.required' => 'Elegí la vacuna aplicada.',
        ]);

        $galpon = $this->resolveGalponParaGuardar($operarioGalponService, 'dialogVacunacionAbierto');

        if ($galpon === null) {
            return;
        }

        $lote = Lote::query()
            ->forEmpresa((int) auth()->user()->empresa_id)
            ->whereKey((int) $validated['loteId'])
            ->first();

        if ($lote === null) {
            $this->addError('loteId', 'El lote seleccionado no es válido.');

            return;
        }

        $registrarVacunacion->execute(
            auth()->user(),
            $galpon,
            $lote,
            VacunaTipo::from($validated['vacuna']),
            null,
        );

        $this->dialogVacunacionAbierto = false;
        $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService);
        $this->dispatch('snackbar-show', message: 'Vacunación guardada.', variant: 'success');
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

    private function resetFormularioVacunacion(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        $galpon = $operarioGalponService->galponActual(auth()->user());
        $loteId = '';

        if ($galpon !== null) {
            $lotes = $operarioGalponResumenService->lotesActivos($galpon);

            if ($lotes->count() === 1) {
                $loteId = (string) $lotes->first()->id;
            }
        }

        $this->reset(['vacuna']);
        $this->loteId = $loteId;
        $this->resetValidation();
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
        ]);
        $this->loteGalponId = $loteGalponId;
        $this->resetValidation();
    }

    private function redirectToHomeConSelectorGalpon(): void
    {
        session()->flash('abrirSelectorGalpon', true);
        $this->redirectRoute('operario.home', navigate: true);
    }
}
