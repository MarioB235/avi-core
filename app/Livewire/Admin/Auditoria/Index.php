<?php

namespace App\Livewire\Admin\Auditoria;

use App\Livewire\Concerns\RequiresAdminModuleAccess;
use App\Models\Auditoria;
use App\Models\User;
use App\Services\AdminAuditoriaConsultaService;
use App\Services\EmpresaContextService;
use App\Services\SoporteEmpresaService;
use App\Support\AdminAuditoriaConsultaFiltros;
use App\Support\AdminFiltroFechasOperativas;
use App\Support\AuditoriaPresentacion;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Auditoría · AviCore')]
class Index extends Component
{
    use RequiresAdminModuleAccess;
    use WithPagination;

    #[Url(as: 'categoria', except: '', history: true)]
    public string $filtroCategoria = '';

    #[Url(as: 'actor', except: '', history: true)]
    public string $filtroActorId = '';

    #[Url(as: 'accion', except: '', history: true)]
    public string $filtroAccion = '';

    #[Url(as: 'desde', except: '', history: true)]
    public ?string $fechaDesde = null;

    #[Url(as: 'hasta', except: '', history: true)]
    public ?string $fechaHasta = null;

    public bool $dialogDetalleAbierto = false;

    public ?int $detalleId = null;

    protected function requiredAdminModuleAbility(): string
    {
        return 'admin.viewAuditoria';
    }

    public function mount(SoporteEmpresaService $soporte): void
    {
        $this->authorizeAdminModule();

        $user = auth()->user();

        if ($user instanceof User && $user->isAdminAvicore() && $soporte->isActive()) {
            $soporte->recordAccionForActiveSession('consulta_auditoria', [
                'ruta' => $soporte->entryRouteName(),
            ]);
        }
    }

    public function updatedFiltroCategoria(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroActorId(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroAccion(): void
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
        $this->filtroCategoria = '';
        $this->filtroActorId = '';
        $this->filtroAccion = '';
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->resetErrorBag('fechaDesde', 'fechaHasta');
        $this->resetPage();
    }

    public function abrirDetalle(int $auditoriaId, AdminAuditoriaConsultaService $consulta): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $auditoria = $consulta->resolverDetalle($user, $auditoriaId);

        if ($auditoria === null) {
            return;
        }

        $this->detalleId = $auditoria->id;
        $this->dialogDetalleAbierto = true;
    }

    public function cerrarDetalle(): void
    {
        $this->dialogDetalleAbierto = false;
        $this->detalleId = null;
    }

    public function render(
        AdminAuditoriaConsultaService $consulta,
        EmpresaContextService $empresaContext,
    ): View {
        /** @var User $user */
        $user = auth()->user();
        $empresaId = $empresaContext->empresaIdFor($user);

        $filtros = new AdminAuditoriaConsultaFiltros(
            categoria: $this->filtroCategoria !== '' ? $this->filtroCategoria : null,
            actorId: $this->filtroActorId !== '' ? (int) $this->filtroActorId : null,
            accion: $this->filtroAccion !== '' ? $this->filtroAccion : null,
            fechaDesde: $this->fechaDesde,
            fechaHasta: $this->fechaHasta,
        );

        $auditorias = $consulta->auditoriasPaginadas($user, $filtros);
        $actores = $consulta->actoresParaFiltro($user);

        $detalle = null;
        $detalleLineas = [];

        if ($this->detalleId !== null) {
            $detalle = $consulta->resolverDetalle($user, $this->detalleId);

            if ($detalle instanceof Auditoria) {
                $detalleLineas = $consulta->detalleLineas($detalle);
            }
        }

        $fechaMaxima = AdminFiltroFechasOperativas::fechaMaxima($empresaId);

        return view('livewire.admin.auditoria.index', [
            'auditorias' => $auditorias,
            'actoresOptions' => $actores
                ->mapWithKeys(fn (User $actor): array => [(string) $actor->id => $actor->name])
                ->all(),
            'categoriaOptions' => $consulta->categoriaOptions(),
            'hayFiltrosActivos' => $this->filtroCategoria !== ''
                || $this->filtroActorId !== ''
                || $this->filtroAccion !== ''
                || $this->fechaDesde !== null
                || $this->fechaHasta !== null,
            'fechaMaxima' => $fechaMaxima,
            'detalle' => $detalle,
            'detalleLineas' => $detalleLineas,
            'tituloResumen' => fn (Auditoria $auditoria): string => AuditoriaPresentacion::resumenTitulo($auditoria),
            'subtituloResumen' => fn (Auditoria $auditoria): string => AuditoriaPresentacion::resumenSubtitulo($auditoria),
        ]);
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
