<?php

namespace App\Livewire\Admin\Empresas;

use App\Actions\Empresa\CreateEmpresaAction;
use App\Actions\Empresa\StartSoporteEmpresaAction;
use App\Actions\Empresa\UpdateEmpresaConfiguracionAction;
use App\Actions\Empresa\UpdateEmpresaEstadoAction;
use App\Enums\EmpresaEstado;
use App\Models\Empresa;
use App\Services\EmpresaLogoPathGuard;
use App\Services\SoporteEmpresaService;
use App\Support\EmpresaConfiguracion;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Empresas · AviCore')]
class Index extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;

    public string $busqueda = '';

    public bool $dialogFormularioAbierto = false;

    public string $nombre = '';

    public string $codigo = '';

    public string $estado = '';

    public string $admin_name = '';

    public string $admin_documento = '';

    public string $admin_email = '';

    public bool $dialogPasswordAbierto = false;

    public string $plainPassword = '';

    public string $passwordUserName = '';

    public bool $dialogEstadoAbierto = false;

    public ?int $editingEmpresaId = null;

    public string $estadoNuevo = '';

    public string $motivoEstado = '';

    public bool $dialogConfigAbierto = false;

    public ?int $configEmpresaId = null;

    public string $configNombre = '';

    public string $configZonaHoraria = '';

    public string $configHuevosPorMaple = '';

    public string $configMaplesPorCajon = '';

    public ?TemporaryUploadedFile $configLogo = null;

    public bool $configQuitarLogo = false;

    public bool $dialogSoporteAbierto = false;

    public ?int $soporteEmpresaId = null;

    public string $motivoSoporte = '';

    public function mount(): void
    {
        $this->authorizeModuleAccess();
        $this->estado = EmpresaEstado::Activa->value;
    }

    public function hydrate(): void
    {
        $this->authorizeModuleAccess();
    }

    protected function authorizeModuleAccess(): void
    {
        $this->authorize('viewAny', Empresa::class);
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function abrirCrear(): void
    {
        $this->authorize('create', Empresa::class);
        $this->resetFormulario();
        $this->estado = EmpresaEstado::Activa->value;
        $this->dialogFormularioAbierto = true;
    }

    public function cerrarFormulario(): void
    {
        $this->dialogFormularioAbierto = false;
        $this->resetFormulario();
    }

    public function guardar(CreateEmpresaAction $createEmpresa): void
    {
        $this->authorize('create', Empresa::class);

        $result = $createEmpresa->execute(auth()->user(), [
            'nombre' => $this->nombre,
            'codigo' => $this->codigo,
            'estado' => $this->estado,
            'admin_name' => $this->admin_name,
            'admin_documento' => $this->admin_documento,
            'admin_email' => $this->admin_email !== '' ? $this->admin_email : null,
        ]);

        $this->cerrarFormulario();
        $this->mostrarPasswordTemporal($result['admin']->name, $result['plainPassword']);
        $this->dispatch('snackbar-show', message: 'Empresa creada.', variant: 'success');
    }

    public function cerrarPassword(): void
    {
        $this->dialogPasswordAbierto = false;
        $this->plainPassword = '';
        $this->passwordUserName = '';
    }

    public function abrirCambioEstado(int $empresaId): void
    {
        $empresa = $this->findEmpresa($empresaId);
        $this->authorize('updateEstado', $empresa);

        $this->editingEmpresaId = $empresa->id;
        $this->estadoNuevo = $empresa->estado->value;
        $this->motivoEstado = '';
        $this->resetValidation();
        $this->dialogEstadoAbierto = true;
    }

    public function cerrarCambioEstado(): void
    {
        $this->dialogEstadoAbierto = false;
        $this->editingEmpresaId = null;
        $this->estadoNuevo = '';
        $this->motivoEstado = '';
        $this->resetValidation();
    }

    public function guardarEstado(UpdateEmpresaEstadoAction $updateEmpresaEstado): void
    {
        $empresa = $this->findEmpresa((int) $this->editingEmpresaId);
        $this->authorize('updateEstado', $empresa);

        $updateEmpresaEstado->execute(auth()->user(), $empresa, [
            'estado' => $this->estadoNuevo,
            'motivo' => $this->motivoEstado,
        ]);

        $this->cerrarCambioEstado();
        $this->dispatch('snackbar-show', message: 'Estado de empresa actualizado.', variant: 'success');
    }

    public function abrirConfigurar(int $empresaId): void
    {
        $empresa = $this->findEmpresa($empresaId);
        $this->authorize('update', $empresa);

        $operativa = $empresa->configuracionOperativa();

        $this->configEmpresaId = $empresa->id;
        $this->configNombre = $empresa->nombre;
        $this->configZonaHoraria = $operativa->zonaHoraria;
        $this->configHuevosPorMaple = (string) $operativa->huevosPorMaple;
        $this->configMaplesPorCajon = (string) $operativa->maplesPorCajon;
        $this->configLogo = null;
        $this->configQuitarLogo = false;
        $this->resetValidation();
        $this->dialogConfigAbierto = true;
    }

    public function cerrarConfigurar(): void
    {
        $this->dialogConfigAbierto = false;
        $this->configEmpresaId = null;
        $this->configNombre = '';
        $this->configZonaHoraria = '';
        $this->configHuevosPorMaple = '';
        $this->configMaplesPorCajon = '';
        $this->configLogo = null;
        $this->configQuitarLogo = false;
        $this->resetValidation();
    }

    public function abrirSoporte(int $empresaId): void
    {
        $empresa = $this->findEmpresa($empresaId);
        $this->authorize('enterSupport', $empresa);

        $this->soporteEmpresaId = $empresa->id;
        $this->motivoSoporte = '';
        $this->resetValidation();
        $this->dialogSoporteAbierto = true;
    }

    public function cerrarSoporte(): void
    {
        $this->dialogSoporteAbierto = false;
        $this->soporteEmpresaId = null;
        $this->motivoSoporte = '';
        $this->resetValidation();
    }

    public function ingresarSoporte(StartSoporteEmpresaAction $startSoporte)
    {
        $empresa = $this->findEmpresa((int) $this->soporteEmpresaId);
        $this->authorize('enterSupport', $empresa);

        $startSoporte->execute(auth()->user(), $empresa, [
            'motivo' => $this->motivoSoporte,
        ]);

        $this->cerrarSoporte();
        $this->dispatch('snackbar-show', message: 'Ingresaste en modo soporte.', variant: 'success');

        return $this->redirect(route(app(SoporteEmpresaService::class)->entryRouteName()), navigate: true);
    }

    public function guardarConfiguracion(UpdateEmpresaConfiguracionAction $updateConfiguracion): void
    {
        $empresa = $this->findEmpresa((int) $this->configEmpresaId);
        $this->authorize('update', $empresa);

        $updateConfiguracion->execute(auth()->user(), $empresa, [
            'nombre' => $this->configNombre,
            'zona_horaria' => $this->configZonaHoraria,
            'huevos_por_maple' => (int) $this->configHuevosPorMaple,
            'maples_por_cajon' => (int) $this->configMaplesPorCajon,
            'quitar_logo' => $this->configQuitarLogo,
            'logo' => $this->configLogo,
        ]);

        $this->cerrarConfigurar();
        $this->dispatch('snackbar-show', message: 'Configuración guardada.', variant: 'success');
    }

    public function render(): View
    {
        $actor = auth()->user();

        $query = Empresa::query()
            ->withCount('users')
            ->orderBy('nombre');

        if ($this->busqueda !== '') {
            $term = '%'.$this->busqueda.'%';
            $query->where(function ($builder) use ($term): void {
                $builder
                    ->where('nombre', 'ilike', $term)
                    ->orWhere('codigo', 'ilike', $term);
            });
        }

        $estadoOptions = collect(EmpresaEstado::cases())
            ->mapWithKeys(fn (EmpresaEstado $estado): array => [$estado->value => $estado->label()])
            ->all();

        $zonaHorariaOptions = collect(EmpresaConfiguracion::zonasHorariasPermitidas())
            ->mapWithKeys(fn (string $zone): array => [$zone => str_replace('_', ' ', $zone)])
            ->all();

        $configEmpresa = $this->configEmpresaId !== null
            ? Empresa::query()->find($this->configEmpresaId)
            : null;

        $logoGuard = app(EmpresaLogoPathGuard::class);

        return view('livewire.admin.empresas.index', [
            'empresas' => $query->paginate(15),
            'actor' => $actor,
            'estadoOptions' => $estadoOptions,
            'zonaHorariaOptions' => $zonaHorariaOptions,
            'canCreate' => Gate::forUser($actor)->allows('create', Empresa::class),
            'editingEmpresa' => $this->editingEmpresaId !== null
                ? Empresa::query()->find($this->editingEmpresaId)
                : null,
            'configEmpresa' => $configEmpresa,
            'configLogoUrl' => $configEmpresa !== null
                ? $logoGuard->publicUrl($configEmpresa->logo_path)
                : null,
            'soporteEmpresa' => $this->soporteEmpresaId !== null
                ? Empresa::query()->find($this->soporteEmpresaId)
                : null,
        ]);
    }

    private function findEmpresa(int $empresaId): Empresa
    {
        $empresa = Empresa::query()->findOrFail($empresaId);
        $this->authorize('view', $empresa);

        return $empresa;
    }

    private function resetFormulario(): void
    {
        $this->nombre = '';
        $this->codigo = '';
        $this->estado = EmpresaEstado::Activa->value;
        $this->admin_name = '';
        $this->admin_documento = '';
        $this->admin_email = '';
        $this->resetValidation();
    }

    private function mostrarPasswordTemporal(string $userName, string $plainPassword): void
    {
        $this->passwordUserName = $userName;
        $this->plainPassword = $plainPassword;
        $this->dialogPasswordAbierto = true;
    }
}
