<?php

namespace App\Livewire\Operario;

use App\Actions\Operacion\AnularRegistroOperativoAction;
use App\Actions\Operacion\AnularVacunacionAction;
use App\Livewire\Operario\Concerns\ManagesGalponSelector;
use App\Models\RegistroOperativo;
use App\Models\Vacunacion;
use App\Services\OperarioGalponService;
use App\Support\OperarioHistorialItem;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.operario-mobile')]
#[Title('Historial')]
class Historial extends Component
{
    use ManagesGalponSelector;
    use WithPagination;

    #[Url(as: 'fecha', history: true)]
    public ?string $fecha = null;

    public bool $dialogDetalleAbierto = false;

    public ?string $detalleKey = null;

    public bool $mostrarFormularioAnulacion = false;

    public string $motivoAnulacion = '';

    public function mount(OperarioGalponService $operarioGalponService): void
    {
        $this->bootGalponSelector($operarioGalponService);
        $this->validarFecha();
    }

    public function updatedFecha(): void
    {
        $this->validarFecha();
        $this->resetPage();
    }

    public function verTodasLasFechas(): void
    {
        $this->fecha = null;
        $this->resetPage();
    }

    public function abrirDetalle(string $key): void
    {
        $item = $this->resolverDetalle($key);

        if ($item === null) {
            return;
        }

        $this->detalleKey = $key;
        $this->mostrarFormularioAnulacion = false;
        $this->motivoAnulacion = '';
        $this->resetValidation();
        $this->dialogDetalleAbierto = true;
    }

    public function cerrarDetalle(): void
    {
        $this->dialogDetalleAbierto = false;
        $this->detalleKey = null;
        $this->mostrarFormularioAnulacion = false;
        $this->motivoAnulacion = '';
        $this->resetValidation();
    }

    public function mostrarAnulacion(): void
    {
        $item = $this->resolverDetalle($this->detalleKey);

        if ($item === null || ! $item->puedeAnular || $item->anulado) {
            return;
        }

        $this->mostrarFormularioAnulacion = true;
    }

    public function cancelarAnulacion(): void
    {
        $this->mostrarFormularioAnulacion = false;
        $this->motivoAnulacion = '';
        $this->resetValidation(['motivoAnulacion']);
    }

    public function anularRegistro(
        AnularRegistroOperativoAction $anularRegistro,
        AnularVacunacionAction $anularVacunacion,
    ): void {
        $user = auth()->user();
        $item = $this->resolverDetalle($this->detalleKey);

        if ($user === null || $item === null || ! $item->puedeAnular || $item->anulado) {
            return;
        }

        try {
            if ($item->sourceType === 'registro') {
                $registro = RegistroOperativo::query()
                    ->forEmpresa((int) $user->empresa_id)
                    ->where('user_id', $user->id)
                    ->findOrFail($item->sourceId);

                $anularRegistro->execute($user, $registro, $this->motivoAnulacion);
            } else {
                $vacunacion = Vacunacion::query()
                    ->forEmpresa((int) $user->empresa_id)
                    ->where('user_id', $user->id)
                    ->findOrFail($item->sourceId);

                $anularVacunacion->execute($user, $vacunacion, $this->motivoAnulacion);
            }
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->cerrarDetalle();
        $this->dispatch('snackbar-show', message: 'Registro anulado.', variant: 'success');
    }

    public function render(OperarioGalponService $operarioGalponService): View
    {
        $user = auth()->user();

        $galpon = $user ? $operarioGalponService->galponActual($user) : null;

        $registros = $operarioGalponService
            ->historialPaginado($user, $this->fecha, 20, $this->getPage());

        $todosRegistrosAnulados = $registros->isNotEmpty()
            && $registros->every(fn (OperarioHistorialItem $item): bool => $item->anulado);

        return view('livewire.operario.historial', [
            'galpon' => $galpon,
            'galpones' => $operarioGalponService->galponesDisponibles($user),
            'registros' => $registros,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
            'fechaError' => $this->getErrorBag()->first('fecha'),
            'detalleItem' => $this->resolverDetalle($this->detalleKey),
            'todosRegistrosAnulados' => $todosRegistrosAnulados,
        ]);
    }

    private function resolverDetalle(?string $key): ?OperarioHistorialItem
    {
        $user = auth()->user();

        if ($user === null || $key === null || $key === '') {
            return null;
        }

        return OperarioHistorialItem::resolve($key, $user);
    }

    private function validarFecha(): void
    {
        if ($this->fecha === null || $this->fecha === '') {
            $this->fecha = null;
            $this->resetErrorBag('fecha');

            return;
        }

        $validator = validator(
            ['fecha' => $this->fecha],
            ['fecha' => ['required', 'date', 'before_or_equal:today']],
            [
                'fecha.date' => 'La fecha seleccionada no es válida.',
                'fecha.before_or_equal' => 'La fecha no puede ser futura.',
            ],
        );

        if ($validator->fails()) {
            $this->fecha = null;
            $this->resetErrorBag('fecha');
            $this->addError('fecha', (string) $validator->errors()->first('fecha'));

            return;
        }

        $this->resetErrorBag('fecha');
    }
}
