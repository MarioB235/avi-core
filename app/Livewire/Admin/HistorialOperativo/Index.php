<?php

namespace App\Livewire\Admin\HistorialOperativo;

use App\Actions\Auditoria\CorregirRegistroOperativoAction;
use App\Enums\RegistroOperativoEstado;
use App\Enums\RegistroOperativoTipo;
use App\Livewire\Concerns\RequiresAdminModuleAccess;
use App\Models\RegistroOperativo;
use App\Models\User;
use App\Services\AdminHistorialOperativoService;
use App\Services\EmpresaContextService;
use App\Services\SoporteEmpresaService;
use App\Support\AdminFiltroFechasOperativas;
use App\Support\AdminHistorialOperativoFiltros;
use App\Support\SupervisorHistorialItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Historial operativo · AviCore')]
class Index extends Component
{
    use RequiresAdminModuleAccess;
    use WithPagination;

    #[Url(as: 'granja', except: '', history: true)]
    public string $filtroGranjaId = '';

    #[Url(as: 'galpon', except: '', history: true)]
    public string $filtroGalponId = '';

    #[Url(as: 'operario', except: '', history: true)]
    public string $filtroOperarioId = '';

    #[Url(as: 'tipo', except: '', history: true)]
    public string $filtroTipo = '';

    #[Url(as: 'estado', except: '', history: true)]
    public string $filtroEstado = '';

    #[Url(as: 'desde', except: '', history: true)]
    public ?string $fechaDesde = null;

    #[Url(as: 'hasta', except: '', history: true)]
    public ?string $fechaHasta = null;

    public bool $dialogDetalleAbierto = false;

    public ?string $detalleKey = null;

    public bool $mostrarFormularioCorreccion = false;

    public string $motivoCorreccion = '';

    public ?string $fechaEfectivaCorreccion = null;

    public string $corregirHuevos = '';

    public string $corregirHuevosDescarte = '';

    public string $corregirMuertes = '';

    public string $corregirDescarteAves = '';

    public string $corregirAlimentoKg = '';

    protected function requiredAdminModuleAbility(): string
    {
        return 'admin.viewHistorialOperativo';
    }

    public function mount(SoporteEmpresaService $soporte): void
    {
        $this->authorizeAdminModule();

        $user = auth()->user();

        if ($user instanceof User && $user->isAdminAvicore() && $soporte->isActive()) {
            $soporte->recordAccionForActiveSession('consulta_historial_operativo', [
                'ruta' => $soporte->entryRouteName(),
            ]);
        }
    }

    public function updatedFiltroGranjaId(): void
    {
        $this->filtroGalponId = '';
        $this->resetPage();
    }

    public function updatedFiltroGalponId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroOperarioId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFechaDesde(): void
    {
        $this->validarFechas();
        $this->resetPage();
    }

    public function updatedFechaHasta(): void
    {
        $this->validarFechas();
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->filtroGranjaId = '';
        $this->filtroGalponId = '';
        $this->filtroOperarioId = '';
        $this->filtroTipo = '';
        $this->filtroEstado = '';
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->resetErrorBag('fechaDesde', 'fechaHasta');
        $this->resetPage();
    }

    public function abrirDetalle(string $key, EmpresaContextService $empresaContext): void
    {
        $user = auth()->user();
        $empresaId = $user !== null ? $empresaContext->empresaIdFor($user) : null;
        $item = $this->resolverDetalle($key, $empresaId);

        if ($item === null) {
            return;
        }

        $this->detalleKey = $key;
        $this->resetFormularioCorreccion();
        $this->dialogDetalleAbierto = true;
    }

    public function cerrarDetalle(): void
    {
        $this->dialogDetalleAbierto = false;
        $this->detalleKey = null;
        $this->resetFormularioCorreccion();
    }

    public function mostrarCorreccion(EmpresaContextService $empresaContext): void
    {
        $user = auth()->user();
        $empresaId = $user !== null ? $empresaContext->empresaIdFor($user) : null;
        $item = $this->resolverDetalle($this->detalleKey, $empresaId, $user);

        if ($item === null || ! $item->puedeCorregir || $item->anulado) {
            return;
        }

        $this->precargarValoresCorreccion($item);
        $this->mostrarFormularioCorreccion = true;
    }

    public function cancelarCorreccion(): void
    {
        $this->mostrarFormularioCorreccion = false;
        $this->motivoCorreccion = '';
        $this->fechaEfectivaCorreccion = null;
        $this->resetValidation([
            'motivoCorreccion',
            'fechaEfectivaCorreccion',
            'corregirHuevos',
            'corregirHuevosDescarte',
            'corregirMuertes',
            'corregirDescarteAves',
            'corregirAlimentoKg',
            'correccion',
        ]);
    }

    public function guardarCorreccion(
        CorregirRegistroOperativoAction $corregirRegistro,
        EmpresaContextService $empresaContext,
    ): void {
        $user = auth()->user();
        $empresaId = $user !== null ? $empresaContext->empresaIdFor($user) : null;
        $item = $this->resolverDetalle($this->detalleKey, $empresaId, $user);

        if ($user === null || $item === null || ! $item->puedeCorregir || $item->anulado) {
            return;
        }

        try {
            $registro = RegistroOperativo::query()
                ->forEmpresa((int) $user->empresa_id)
                ->findOrFail($item->sourceId);

            $fechaEfectiva = $this->fechaEfectivaCorreccion !== null && $this->fechaEfectivaCorreccion !== ''
                ? Carbon::parse($this->fechaEfectivaCorreccion)->startOfDay()
                : null;

            $corregirRegistro->execute(
                $user,
                $registro,
                $this->motivoCorreccion,
                $this->valoresCorreccionDesdeFormulario($item),
                $fechaEfectiva,
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->mostrarFormularioCorreccion = false;
        $this->motivoCorreccion = '';
        $this->fechaEfectivaCorreccion = null;
        $this->resetValidation();
        session()->flash('status', 'Corrección registrada.');
    }

    public function render(
        AdminHistorialOperativoService $historialService,
        EmpresaContextService $empresaContext,
    ): View {
        /** @var User $user */
        $user = auth()->user();
        $empresaId = $empresaContext->empresaIdFor($user);

        $granjaId = $this->filtroGranjaId !== '' ? (int) $this->filtroGranjaId : null;
        $granjas = $historialService->granjasParaFiltro($user);
        $galponesFiltro = $historialService->galponesParaFiltro($user, $granjaId);
        $operarios = $historialService->operariosParaFiltro($user);

        $validGalponIds = $galponesFiltro->modelKeys();

        if ($this->filtroGalponId !== '' && ! in_array((int) $this->filtroGalponId, $validGalponIds, true)) {
            $this->filtroGalponId = '';
        }

        $validOperarioIds = $operarios->modelKeys();

        if ($this->filtroOperarioId !== '' && ! in_array((int) $this->filtroOperarioId, $validOperarioIds, true)) {
            $this->filtroOperarioId = '';
        }

        $filtros = new AdminHistorialOperativoFiltros(
            granjaId: $granjaId,
            galponId: $this->filtroGalponId !== '' ? (int) $this->filtroGalponId : null,
            userId: $this->filtroOperarioId !== '' ? (int) $this->filtroOperarioId : null,
            tipo: $this->filtroTipo !== '' ? $this->filtroTipo : null,
            estado: $this->filtroEstado !== '' ? $this->filtroEstado : null,
            fechaDesde: $this->fechaDesde,
            fechaHasta: $this->fechaHasta,
        );

        $registros = $historialService->historialPaginado($user, $filtros, 25, $this->getPage());

        $granjasOptions = $granjas
            ->mapWithKeys(fn ($granja): array => [
                (string) $granja->id => $granja->dicose
                    ? "{$granja->nombre} · DICOSE {$granja->dicose}"
                    : $granja->nombre,
            ])
            ->all();

        $galponesOptions = $galponesFiltro
            ->mapWithKeys(fn ($galpon): array => [
                (string) $galpon->id => $galpon->displayName(),
            ])
            ->all();

        $operariosOptions = $operarios
            ->mapWithKeys(fn ($operario): array => [
                (string) $operario->id => $operario->name,
            ])
            ->all();

        $tipoOptions = collect(RegistroOperativoTipo::cases())
            ->mapWithKeys(fn (RegistroOperativoTipo $tipo): array => [
                $tipo->value => $tipo->label(),
            ])
            ->put('vacunacion', 'Vacunación')
            ->all();

        $estadoOptions = [
            RegistroOperativoEstado::Activo->value => 'Activos',
            RegistroOperativoEstado::Anulado->value => 'Anulados',
        ];

        $hayFiltrosActivos = $this->filtroGranjaId !== ''
            || $this->filtroGalponId !== ''
            || $this->filtroOperarioId !== ''
            || $this->filtroTipo !== ''
            || $this->filtroEstado !== ''
            || $this->fechaDesde !== null
            || $this->fechaHasta !== null;

        return view('livewire.admin.historial-operativo.index', [
            'registros' => $registros,
            'granjasOptions' => $granjasOptions,
            'galponesOptions' => $galponesOptions,
            'galponesFiltro' => $galponesFiltro,
            'operariosOptions' => $operariosOptions,
            'tipoOptions' => $tipoOptions,
            'estadoOptions' => $estadoOptions,
            'hayFiltrosActivos' => $hayFiltrosActivos,
            'detalleItem' => $this->resolverDetalle($this->detalleKey, $empresaId, $user),
            'fechaMaxima' => AdminFiltroFechasOperativas::fechaMaxima($empresaId),
        ]);
    }

    private function resolverDetalle(?string $key, ?int $empresaId, ?User $viewer = null): ?SupervisorHistorialItem
    {
        if ($key === null || $key === '' || $empresaId === null) {
            return null;
        }

        return SupervisorHistorialItem::resolve($key, $empresaId, $viewer);
    }

    private function resetFormularioCorreccion(): void
    {
        $this->mostrarFormularioCorreccion = false;
        $this->motivoCorreccion = '';
        $this->fechaEfectivaCorreccion = null;
        $this->corregirHuevos = '';
        $this->corregirHuevosDescarte = '';
        $this->corregirMuertes = '';
        $this->corregirDescarteAves = '';
        $this->corregirAlimentoKg = '';
        $this->resetValidation();
    }

    private function precargarValoresCorreccion(SupervisorHistorialItem $item): void
    {
        $this->corregirHuevos = (string) ($item->valoresCorregibles['huevos'] ?? '');
        $this->corregirHuevosDescarte = (string) ($item->valoresCorregibles['huevos_descarte'] ?? '');
        $this->corregirMuertes = (string) ($item->valoresCorregibles['muertes'] ?? '');
        $this->corregirDescarteAves = (string) ($item->valoresCorregibles['descarte_aves'] ?? '');
        $this->corregirAlimentoKg = (string) ($item->valoresCorregibles['alimento_kg'] ?? '');
        $this->fechaEfectivaCorreccion = $item->createdAt->toDateString();
    }

    /**
     * @return array<string, mixed>
     */
    private function valoresCorreccionDesdeFormulario(SupervisorHistorialItem $item): array
    {
        if ($item->tipoRegistro === RegistroOperativoTipo::Huevos) {
            return [
                'huevos' => $this->corregirHuevos,
                'huevos_descarte' => $this->corregirHuevosDescarte,
            ];
        }

        if ($item->tipoRegistro === RegistroOperativoTipo::Muertes) {
            return ['muertes' => $this->corregirMuertes];
        }

        if ($item->tipoRegistro === RegistroOperativoTipo::Descarte) {
            return ['descarte_aves' => $this->corregirDescarteAves];
        }

        if ($item->tipoRegistro === RegistroOperativoTipo::Alimento) {
            return ['alimento_kg' => $this->corregirAlimentoKg];
        }

        return [];
    }

    private function validarFechas(): void
    {
        $user = auth()->user();
        $empresaId = $user !== null ? app(EmpresaContextService::class)->empresaIdFor($user) : null;

        foreach (AdminFiltroFechasOperativas::validar($this->fechaDesde, $this->fechaHasta, $empresaId) as $field => $message) {
            $this->addError($field, $message);
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $this->resetErrorBag('fechaDesde', 'fechaHasta');
    }
}
