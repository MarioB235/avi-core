<?php

namespace App\Livewire\Operario;

use App\Enums\TipoHuevo;
use App\Enums\VacunaTipo;
use App\Livewire\Operario\Concerns\ManagesAlimentoForm;
use App\Livewire\Operario\Concerns\ManagesCapturaContexto;
use App\Livewire\Operario\Concerns\ManagesDescarteForm;
use App\Livewire\Operario\Concerns\ManagesGalponSelector;
use App\Livewire\Operario\Concerns\ManagesHuevosForm;
use App\Livewire\Operario\Concerns\ManagesLoteForm;
use App\Livewire\Operario\Concerns\ManagesMuertesForm;
use App\Livewire\Operario\Concerns\ManagesVacunacionForm;
use App\Models\Galpon;
use App\Models\Lote;
use App\Services\EmpresaHuevosUnidad;
use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Cargar')]
class CargarHub extends Component
{
    use ManagesAlimentoForm;
    use ManagesCapturaContexto;
    use ManagesDescarteForm;
    use ManagesGalponSelector;
    use ManagesHuevosForm;
    use ManagesLoteForm;
    use ManagesMuertesForm;
    use ManagesVacunacionForm;

    public function mount(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        $this->bootGalponSelector($operarioGalponService);

        $form = request()->query('form');

        if (! in_array($form, ['huevos', 'muertes', 'descarte', 'vacunacion', 'alimento', 'lote'], true)) {
            return;
        }

        if ($form === 'lote') {
            if (! auth()->user()->rol->canCreateLote()) {
                return;
            }

            $this->resetFormularioLote($operarioGalponService);
            $this->dialogLoteAbierto = true;
            $this->registrarContextoCapturaAlAbrirDialogo();

            return;
        }

        if (! $this->ensureGalponSeleccionado($operarioGalponService)) {
            return;
        }

        if ($form === 'huevos') {
            $this->resetFormularioHuevos();
            $this->dialogHuevosAbierto = true;
            $this->registrarContextoCapturaAlAbrirDialogo();

            return;
        }

        if ($form === 'muertes') {
            $this->resetFormularioMuertes();
            $this->dialogMuertesAbierto = true;
            $this->registrarContextoCapturaAlAbrirDialogo();

            return;
        }

        if ($form === 'descarte') {
            $this->resetFormularioDescarte();
            $this->dialogDescarteAbierto = true;
            $this->registrarContextoCapturaAlAbrirDialogo();

            return;
        }

        if ($form === 'alimento') {
            $this->resetFormularioAlimento();
            $this->dialogAlimentoAbierto = true;
            $this->registrarContextoCapturaAlAbrirDialogo();

            return;
        }

        $this->resetFormularioVacunacion($operarioGalponService, $operarioGalponResumenService);
        $this->dialogVacunacionAbierto = true;
        $this->registrarContextoCapturaAlAbrirDialogo();
    }

    public function hydrate(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): void {
        $galponIdAnterior = $this->galponId;

        $this->hydrateGalponSelector($operarioGalponService);

        $this->revalidarCapturaObsoletaEnHydrate(
            $operarioGalponService,
            $operarioGalponResumenService,
            $galponIdAnterior,
        );
    }

    protected function afterSeleccionarGalpon(
        ?int $galponAnteriorId,
        int $galponNuevoId,
        OperarioGalponService $operarioGalponService,
    ): void {
        if ($galponAnteriorId !== $galponNuevoId && $this->tieneDialogoCapturaAbierto()) {
            $this->invalidarFormulariosCapturaObsoletos(
                $operarioGalponService,
                app(OperarioGalponResumenService::class),
                'Cambió el galpón: reiniciamos el formulario abierto.',
            );

            return;
        }

        $this->dispatch('snackbar-show', message: 'Galpón actualizado.', variant: 'success');
    }

    public function render(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): View {
        $user = auth()->user();
        $galpon = $operarioGalponService->galponActual($user);
        $galpones = $operarioGalponService->galponesDisponibles($user);

        /** @var Collection<int, Lote> $lotesActivos */
        $lotesActivos = $galpon !== null
            ? $operarioGalponResumenService->lotesActivos($galpon)
            : new Collection;

        $resumenGalpon = $galpon !== null
            ? $operarioGalponResumenService->resumen($galpon)
            : null;

        $user->loadMissing('empresa');
        $unidadesHuevo = $user->empresa !== null
            ? EmpresaHuevosUnidad::for($user->empresa)
            : EmpresaHuevosUnidad::defaults();

        return view('livewire.operario.cargar-hub', [
            'galpon' => $galpon,
            'galpones' => $galpones,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
            'lotesActivos' => $lotesActivos,
            'resumenGalpon' => $resumenGalpon,
            'unidadesHuevo' => $unidadesHuevo,
            'vacunas' => VacunaTipo::options(),
            'puedeRegistrarLote' => $user->rol->canCreateLote(),
            'galponesDisponibles' => $galpones,
            'tiposHuevoUi' => TipoHuevo::optionsUi(),
        ]);
    }

    protected function ensureGalponSeleccionado(OperarioGalponService $operarioGalponService): bool
    {
        if ($operarioGalponService->galponActual(auth()->user()) !== null) {
            return true;
        }

        $this->selectorGalponAbierto = true;

        return false;
    }

    protected function resolveGalponParaGuardar(
        OperarioGalponService $operarioGalponService,
        string $dialogProperty,
    ): ?Galpon {
        if ($this->abortarSiCapturaObsoleta($operarioGalponService, app(OperarioGalponResumenService::class))) {
            return null;
        }

        $user = auth()->user();

        if ($user === null || $user->ultimo_galpon_id === null) {
            $this->{$dialogProperty} = false;
            $this->selectorGalponAbierto = true;

            return null;
        }

        $galpon = $operarioGalponService->galponDisponibleParaUsuario(
            $user,
            (int) $user->ultimo_galpon_id,
        );

        if ($galpon === null) {
            $this->{$dialogProperty} = false;
            $this->selectorGalponAbierto = true;

            return null;
        }

        return $galpon;
    }
}
