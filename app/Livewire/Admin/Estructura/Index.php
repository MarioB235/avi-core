<?php

namespace App\Livewire\Admin\Estructura;

use App\Actions\Galpon\CreateGalponAction;
use App\Actions\Galpon\UpdateGalponAction;
use App\Actions\Granja\CreateGranjaAction;
use App\Actions\Granja\UpdateGranjaAction;
use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Lote\TransicionarLoteEstadoAction;
use App\Actions\Lote\UpdateLoteAction;
use App\Enums\GalponEstado;
use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Livewire\Concerns\MapsEstructuraValidationErrors;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use App\Services\EmpresaScopeService;
use App\Services\EstructuraFichaService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Estructura · AviCore')]
class Index extends Component
{
    use AuthorizesRequests;
    use MapsEstructuraValidationErrors;
    use WithPagination;

    #[Url(as: 'seccion', except: 'granjas', history: true)]
    public string $seccion = 'granjas';

    #[Url(as: 'q', except: '', history: true)]
    public string $busqueda = '';

    #[Url(as: 'granja', except: '', history: true)]
    public string $filtroGranjaId = '';

    #[Url(as: 'galpon', except: '', history: true)]
    public string $filtroGalponId = '';

    #[Url(as: 'granja_activa', except: '', history: true)]
    public string $filtroGranjaActiva = '';

    #[Url(as: 'galpon_estado', except: '', history: true)]
    public string $filtroGalponEstado = '';

    #[Url(as: 'lote_estado', except: '', history: true)]
    public string $filtroLoteEstado = '';

    #[Url(as: 'lote_tipo', except: '', history: true)]
    public string $filtroLoteTipo = '';

    public bool $dialogGranjaAbierto = false;

    public ?int $editingGranjaId = null;

    public string $granjaNombre = '';

    public string $granjaCodigo = '';

    public string $granjaDicose = '';

    public string $granjaUbicacion = '';

    public bool $granjaActiva = true;

    public bool $dialogGalponAbierto = false;

    public ?int $editingGalponId = null;

    public string $galponGranjaId = '';

    public string $galponNombre = '';

    public string $galponCodigo = '';

    public string $galponCapacidad = '';

    public string $galponEstado = '';

    public bool $galponActivo = true;

    public string $galponObservacion = '';

    public bool $dialogLoteCrearAbierto = false;

    public string $loteGalponId = '';

    public string $loteCodigoSma = '';

    public string $loteTipoHuevo = '';

    public string $loteCantidad = '';

    public string $loteFechaNacimiento = '';

    public bool $dialogLoteEditarAbierto = false;

    public ?int $editingLoteId = null;

    public string $loteEstadoActual = '';

    public string $loteLineaRaza = '';

    public string $loteObservacion = '';

    public bool $dialogLoteTransicionAbierto = false;

    public string $loteTransicionEstado = '';

    public string $loteTransicionMotivo = '';

    public bool $dialogGalponFichaAbierto = false;

    public ?int $fichaGalponId = null;

    public bool $dialogLoteFichaAbierto = false;

    public ?int $fichaLoteId = null;

    public function mount(): void
    {
        $this->authorizeModuleAccess();

        if (! in_array($this->seccion, ['granjas', 'galpones', 'lotes'], true)) {
            $this->seccion = 'granjas';
        }
    }

    public function hydrate(): void
    {
        $this->authorizeModuleAccess();
    }

    protected function authorizeModuleAccess(): void
    {
        $this->authorize('viewAny', Granja::class);
    }

    public function updatedSeccion(): void
    {
        $this->resetPage();
        $this->busqueda = '';
        $this->filtroGranjaActiva = '';
        $this->filtroGalponEstado = '';
        $this->filtroLoteEstado = '';
        $this->filtroLoteTipo = '';

        if ($this->seccion === 'granjas') {
            $this->filtroGranjaId = '';
            $this->filtroGalponId = '';
        }

        if ($this->seccion === 'galpones') {
            $this->filtroGalponId = '';
        }
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroGranjaId(): void
    {
        $this->resetPage();
        $this->filtroGalponId = '';
    }

    public function updatingFiltroGalponId(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroGranjaActiva(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroGalponEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroLoteEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroLoteTipo(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';

        if ($this->seccion === 'granjas') {
            $this->filtroGranjaActiva = '';
        }

        if ($this->seccion === 'galpones') {
            $this->filtroGranjaId = '';
            $this->filtroGalponEstado = '';
        }

        if ($this->seccion === 'lotes') {
            $this->filtroGranjaId = '';
            $this->filtroGalponId = '';
            $this->filtroLoteEstado = '';
            $this->filtroLoteTipo = '';
        }

        $this->resetPage();
    }

    public function abrirCrearGranja(): void
    {
        $this->authorize('create', Granja::class);
        $this->resetGranjaFormulario();
        $this->dialogGranjaAbierto = true;
    }

    public function abrirEditarGranja(int $granjaId): void
    {
        $granja = $this->findScopedGranja($granjaId);
        $this->authorize('update', $granja);

        $this->editingGranjaId = $granja->id;
        $this->granjaNombre = $granja->nombre;
        $this->granjaCodigo = (string) ($granja->codigo ?? '');
        $this->granjaDicose = (string) ($granja->dicose ?? '');
        $this->granjaUbicacion = (string) ($granja->ubicacion ?? '');
        $this->granjaActiva = $granja->activa;
        $this->dialogGranjaAbierto = true;
        $this->resetValidation();
    }

    public function cerrarGranja(): void
    {
        $this->dialogGranjaAbierto = false;
        $this->resetGranjaFormulario();
    }

    public function guardarGranja(CreateGranjaAction $createGranja, UpdateGranjaAction $updateGranja): void
    {
        $payload = [
            'nombre' => $this->granjaNombre,
            'codigo' => $this->granjaCodigo !== '' ? $this->granjaCodigo : null,
            'dicose' => $this->granjaDicose !== '' ? $this->granjaDicose : null,
            'ubicacion' => $this->granjaUbicacion !== '' ? $this->granjaUbicacion : null,
            'activa' => $this->granjaActiva,
        ];

        try {
            if ($this->editingGranjaId !== null) {
                $granja = $this->findScopedGranja($this->editingGranjaId);
                $updateGranja->execute(auth()->user(), $granja, $payload);
                $mensaje = 'Granja actualizada.';
            } else {
                $createGranja->execute(auth()->user(), $payload);
                $mensaje = 'Granja creada.';
            }
        } catch (ValidationException $exception) {
            $this->mapearErroresGranja($exception);

            return;
        }

        $this->cerrarGranja();
        $this->dispatch('snackbar-show', message: $mensaje, variant: 'success');
    }

    public function abrirCrearGalpon(): void
    {
        $this->authorize('create', Galpon::class);
        $this->resetGalponFormulario();
        if ($this->filtroGranjaId !== '') {
            $this->galponGranjaId = $this->filtroGranjaId;
        }
        $this->dialogGalponAbierto = true;
    }

    public function abrirEditarGalpon(int $galponId): void
    {
        $galpon = $this->findScopedGalpon($galponId);
        $this->authorize('update', $galpon);

        $this->editingGalponId = $galpon->id;
        $this->galponGranjaId = (string) $galpon->granja_id;
        $this->galponNombre = $galpon->nombre;
        $this->galponCodigo = (string) ($galpon->codigo ?? '');
        $this->galponCapacidad = $galpon->capacidad !== null ? (string) $galpon->capacidad : '';
        $this->galponEstado = $galpon->estado->value;
        $this->galponActivo = $galpon->activo;
        $this->galponObservacion = (string) ($galpon->observacion ?? '');
        $this->dialogGalponAbierto = true;
        $this->resetValidation();
    }

    public function cerrarGalpon(): void
    {
        $this->dialogGalponAbierto = false;
        $this->resetGalponFormulario();
    }

    public function guardarGalpon(CreateGalponAction $createGalpon, UpdateGalponAction $updateGalpon): void
    {
        $payload = [
            'granja_id' => (int) $this->galponGranjaId,
            'nombre' => $this->galponNombre,
            'codigo' => $this->galponCodigo !== '' ? $this->galponCodigo : null,
            'capacidad' => $this->galponCapacidad !== '' ? (int) $this->galponCapacidad : null,
            'estado' => $this->galponEstado !== '' ? $this->galponEstado : GalponEstado::Activo->value,
            'activo' => $this->galponActivo,
            'observacion' => $this->galponObservacion !== '' ? $this->galponObservacion : null,
        ];

        try {
            if ($this->editingGalponId !== null) {
                $galpon = $this->findScopedGalpon($this->editingGalponId);
                $updateGalpon->execute(auth()->user(), $galpon, $payload);
                $mensaje = 'Galpón actualizado.';
            } else {
                $createGalpon->execute(auth()->user(), $payload);
                $mensaje = 'Galpón creado.';
            }
        } catch (ValidationException $exception) {
            $this->mapearErroresGalpon($exception);

            return;
        }

        $this->cerrarGalpon();
        $this->dispatch('snackbar-show', message: $mensaje, variant: 'success');
    }

    public function abrirCrearLote(): void
    {
        $this->authorize('create', Lote::class);
        $this->resetLoteCrearFormulario();
        if ($this->filtroGalponId !== '') {
            $this->loteGalponId = $this->filtroGalponId;
        }
        $this->dialogLoteCrearAbierto = true;
    }

    public function cerrarLoteCrear(): void
    {
        $this->dialogLoteCrearAbierto = false;
        $this->resetLoteCrearFormulario();
    }

    public function guardarLoteCrear(RegistrarLoteAction $registrarLote): void
    {
        $this->authorize('create', Lote::class);

        try {
            $galpon = $this->findScopedGalpon((int) $this->loteGalponId);
            $tipo = TipoHuevo::from($this->loteTipoHuevo);

            $lotes = $registrarLote->execute(
                auth()->user(),
                $galpon,
                [$tipo->value => (int) $this->loteCantidad],
                Carbon::parse($this->loteFechaNacimiento),
                codigoSma: $this->loteCodigoSma !== '' ? $this->loteCodigoSma : null,
            );
        } catch (ValidationException $exception) {
            $this->mapearErroresLote($exception);

            return;
        } catch (\ValueError) {
            $this->addError('loteTipoHuevo', 'Elegí un tipo de ave válido.');

            return;
        }

        $this->cerrarLoteCrear();
        $codigos = $lotes->pluck('codigo')->implode(', ');
        $this->dispatch('snackbar-show', message: "Lote {$codigos} registrado.", variant: 'success');
    }

    public function abrirEditarLote(int $loteId): void
    {
        $lote = $this->findScopedLote($loteId);
        $this->authorize('update', $lote);

        $this->editingLoteId = $lote->id;
        $this->loteCodigoSma = (string) ($lote->codigo_sma ?? '');
        $this->loteLineaRaza = (string) ($lote->linea_raza ?? '');
        $this->loteEstadoActual = $lote->estado->value;
        $this->loteObservacion = (string) ($lote->observacion ?? '');
        $this->dialogLoteEditarAbierto = true;
        $this->resetValidation();
    }

    public function cerrarLoteEditar(): void
    {
        $this->dialogLoteEditarAbierto = false;
        $this->editingLoteId = null;
        $this->loteCodigoSma = '';
        $this->loteLineaRaza = '';
        $this->loteEstadoActual = '';
        $this->loteObservacion = '';
        $this->resetValidation();
    }

    public function abrirTransicionLote(): void
    {
        $lote = $this->findScopedLote((int) $this->editingLoteId);
        $this->authorize('transition', $lote);

        $this->loteTransicionEstado = '';
        $this->loteTransicionMotivo = '';
        $this->dialogLoteTransicionAbierto = true;
        $this->resetValidation(['loteTransicionEstado', 'loteTransicionMotivo']);
    }

    public function cerrarTransicionLote(): void
    {
        $this->dialogLoteTransicionAbierto = false;
        $this->loteTransicionEstado = '';
        $this->loteTransicionMotivo = '';
        $this->resetValidation(['loteTransicionEstado', 'loteTransicionMotivo']);
    }

    public function guardarLoteEditar(UpdateLoteAction $updateLote): void
    {
        $lote = $this->findScopedLote((int) $this->editingLoteId);

        try {
            $updateLote->execute(auth()->user(), $lote, [
                'codigo_sma' => $this->loteCodigoSma !== '' ? $this->loteCodigoSma : null,
                'linea_raza' => $this->loteLineaRaza !== '' ? $this->loteLineaRaza : null,
                'observacion' => $this->loteObservacion !== '' ? $this->loteObservacion : null,
            ]);
        } catch (ValidationException $exception) {
            $this->mapearErroresLote($exception);

            return;
        }

        $this->cerrarLoteEditar();
        $this->dispatch('snackbar-show', message: 'Lote actualizado.', variant: 'success');
    }

    public function guardarLoteTransicion(TransicionarLoteEstadoAction $transicionarLote): void
    {
        $lote = $this->findScopedLote((int) $this->editingLoteId);

        try {
            $lote = $transicionarLote->execute(auth()->user(), $lote, [
                'estado' => $this->loteTransicionEstado,
                'motivo' => $this->loteTransicionMotivo,
            ]);
        } catch (ValidationException $exception) {
            $this->mapearErroresLoteTransicion($exception);

            return;
        }

        $this->loteEstadoActual = $lote->estado->value;
        $this->cerrarTransicionLote();
        $this->dispatch('snackbar-show', message: 'Estado del lote actualizado.', variant: 'success');
    }

    public function abrirFichaGalpon(int $galponId): void
    {
        $this->findScopedGalpon($galponId);

        $this->fichaGalponId = $galponId;
        $this->dialogGalponFichaAbierto = true;
    }

    public function cerrarFichaGalpon(): void
    {
        $this->dialogGalponFichaAbierto = false;
        $this->fichaGalponId = null;
    }

    public function abrirFichaLote(int $loteId): void
    {
        $this->findScopedLote($loteId);

        $this->fichaLoteId = $loteId;
        $this->dialogLoteFichaAbierto = true;
    }

    public function cerrarFichaLote(): void
    {
        $this->dialogLoteFichaAbierto = false;
        $this->fichaLoteId = null;
    }

    public function render(): View
    {
        $actor = auth()->user();
        $empresaId = $actor->empresa_id;

        $granjasOptions = Granja::query()
            ->when($empresaId !== null, fn ($q) => $q->where('empresa_id', $empresaId))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'dicose'])
            ->mapWithKeys(fn (Granja $granja): array => [
                (string) $granja->id => $granja->dicose
                    ? "{$granja->nombre} · DICOSE {$granja->dicose}"
                    : $granja->nombre,
            ])
            ->all();

        $galponesOptions = Galpon::query()
            ->when($empresaId !== null, fn ($q) => $q->where('empresa_id', $empresaId))
            ->when($this->filtroGranjaId !== '', fn ($q) => $q->where('granja_id', (int) $this->filtroGranjaId))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo'])
            ->mapWithKeys(fn (Galpon $galpon): array => [(string) $galpon->id => $galpon->displayName()])
            ->all();

        $galponEnEdicion = $this->editingGalponId !== null && $this->dialogGalponAbierto
            ? $this->findScopedGalpon($this->editingGalponId)
            : null;

        $fichaService = app(EstructuraFichaService::class);

        $fichaGalpon = $this->dialogGalponFichaAbierto && $this->fichaGalponId !== null
            ? $fichaService->galpon($this->findScopedGalpon($this->fichaGalponId))
            : null;

        $fichaLote = $this->dialogLoteFichaAbierto && $this->fichaLoteId !== null
            ? $fichaService->lote($this->findScopedLote($this->fichaLoteId))
            : null;

        return view('livewire.admin.estructura.index', [
            'actor' => $actor,
            'canManageEstructura' => $actor->rol->canManageEstructura(),
            'galponEditBloqueaReasignacionGranja' => $galponEnEdicion?->tieneHistorialTrazable() ?? false,
            'galponEditGranjaNombre' => $galponEnEdicion?->granja?->nombre,
            'canManageLotes' => Gate::forUser($actor)->allows('create', Lote::class),
            'granjas' => $this->seccion === 'granjas' ? $this->granjasQuery()->paginate(15) : null,
            'galpones' => $this->seccion === 'galpones' ? $this->galponesQuery()->paginate(15) : null,
            'lotes' => $this->seccion === 'lotes' ? $this->lotesQuery()->paginate(15) : null,
            'granjasOptions' => $granjasOptions,
            'galponesOptions' => $galponesOptions,
            'galponEstadoOptions' => GalponEstado::options(),
            'loteEstadoOptions' => LoteEstado::options(),
            'loteTransicionEstadoOptions' => $this->loteTransicionEstadoOptions(),
            'tipoHuevoOptions' => TipoHuevo::optionsUi(),
            'granjaActivaOptions' => [
                '1' => 'Activas',
                '0' => 'Inactivas',
            ],
            'filtrosActivos' => $this->filtrosActivosEnSeccion(),
            'emptyListadoMensaje' => $this->emptyListadoMensaje(),
            'fichaGalpon' => $fichaGalpon,
            'fichaLote' => $fichaLote,
        ]);
    }

    public function filtrosActivosEnSeccion(): bool
    {
        return match ($this->seccion) {
            'granjas' => $this->busqueda !== '' || $this->filtroGranjaActiva !== '',
            'galpones' => $this->busqueda !== ''
                || $this->filtroGranjaId !== ''
                || $this->filtroGalponEstado !== '',
            'lotes' => $this->busqueda !== ''
                || $this->filtroGranjaId !== ''
                || $this->filtroGalponId !== ''
                || $this->filtroLoteEstado !== ''
                || $this->filtroLoteTipo !== '',
            default => false,
        };
    }

    public function emptyListadoMensaje(): string
    {
        if ($this->filtrosActivosEnSeccion()) {
            return 'No hay resultados con los filtros actuales. Probá ampliar la búsqueda o limpiar filtros.';
        }

        return match ($this->seccion) {
            'granjas' => 'Registrá la primera granja de tu empresa con su DICOSE.',
            'galpones' => 'Creá un galpón dentro de una granja activa.',
            'lotes' => 'Registrá un lote en un galpón disponible.',
            default => 'No hay registros para mostrar.',
        };
    }

    /**
     * @return array<string, string>
     */
    private function loteTransicionEstadoOptions(): array
    {
        if ($this->editingLoteId === null) {
            return [];
        }

        $actor = auth()->user();

        $lote = Lote::query()
            ->when($actor->empresa_id !== null, fn ($q) => $q->where('empresa_id', $actor->empresa_id))
            ->whereKey($this->editingLoteId)
            ->first();

        if ($lote === null) {
            return [];
        }

        return collect($lote->estado->transicionesPermitidas($actor->rol->canReabrirLote()))
            ->mapWithKeys(fn (LoteEstado $estado): array => [$estado->value => $estado->label()])
            ->all();
    }

    private function granjasQuery()
    {
        $actor = auth()->user();

        return Granja::query()
            ->when($actor->empresa_id !== null, fn ($q) => $q->where('empresa_id', $actor->empresa_id))
            ->when($this->busqueda !== '', function ($query): void {
                $term = '%'.$this->busqueda.'%';
                $query->where(function ($builder) use ($term): void {
                    $builder
                        ->where('nombre', 'ilike', $term)
                        ->orWhere('codigo', 'ilike', $term)
                        ->orWhere('dicose', 'ilike', $term)
                        ->orWhere('ubicacion', 'ilike', $term);
                });
            })
            ->when($this->filtroGranjaActiva === '1', fn ($q) => $q->where('activa', true))
            ->when($this->filtroGranjaActiva === '0', fn ($q) => $q->where('activa', false))
            ->orderBy('nombre');
    }

    private function galponesQuery()
    {
        $actor = auth()->user();

        return Galpon::query()
            ->with('granja')
            ->when($actor->empresa_id !== null, fn ($q) => $q->where('empresa_id', $actor->empresa_id))
            ->when($this->filtroGranjaId !== '', fn ($q) => $q->where('granja_id', (int) $this->filtroGranjaId))
            ->when($this->filtroGalponEstado !== '', fn ($q) => $q->where('estado', $this->filtroGalponEstado))
            ->when($this->busqueda !== '', function ($query): void {
                $term = '%'.$this->busqueda.'%';
                $query->where(function ($builder) use ($term): void {
                    $builder
                        ->where('nombre', 'ilike', $term)
                        ->orWhere('codigo', 'ilike', $term);
                });
            })
            ->orderBy('nombre');
    }

    private function lotesQuery()
    {
        $actor = auth()->user();

        return Lote::query()
            ->with(['galpon.granja'])
            ->when($actor->empresa_id !== null, fn ($q) => $q->where('empresa_id', $actor->empresa_id))
            ->when($this->filtroGalponId !== '', fn ($q) => $q->where('galpon_id', (int) $this->filtroGalponId))
            ->when($this->filtroGranjaId !== '', function ($query): void {
                $query->whereHas('galpon', fn ($q) => $q->where('granja_id', (int) $this->filtroGranjaId));
            })
            ->when($this->filtroLoteEstado !== '', fn ($q) => $q->where('estado', $this->filtroLoteEstado))
            ->when($this->filtroLoteTipo !== '', fn ($q) => $q->where('tipo_huevo', $this->filtroLoteTipo))
            ->when($this->busqueda !== '', function ($query): void {
                $term = '%'.$this->busqueda.'%';
                $query->where(function ($builder) use ($term): void {
                    $builder
                        ->where('codigo', 'ilike', $term)
                        ->orWhere('codigo_sma', 'ilike', $term)
                        ->orWhere('linea_raza', 'ilike', $term);
                });
            })
            ->orderByDesc('fecha_ingreso')
            ->orderBy('codigo');
    }

    private function findScopedGranja(int $granjaId): Granja
    {
        $actor = auth()->user();
        $granja = app(EmpresaScopeService::class)->findForActor(Granja::query(), $actor, $granjaId);
        $this->authorize('view', $granja);

        return $granja;
    }

    private function findScopedGalpon(int $galponId): Galpon
    {
        $actor = auth()->user();
        $galpon = app(EmpresaScopeService::class)->findForActor(Galpon::query(), $actor, $galponId);
        $this->authorize('view', $galpon);

        return $galpon;
    }

    private function findScopedLote(int $loteId): Lote
    {
        $actor = auth()->user();
        $lote = app(EmpresaScopeService::class)->findForActor(Lote::query(), $actor, $loteId);
        $this->authorize('view', $lote);

        return $lote;
    }

    private function resetGranjaFormulario(): void
    {
        $this->editingGranjaId = null;
        $this->granjaNombre = '';
        $this->granjaCodigo = '';
        $this->granjaDicose = '';
        $this->granjaUbicacion = '';
        $this->granjaActiva = true;
        $this->resetValidation();
    }

    private function resetGalponFormulario(): void
    {
        $this->editingGalponId = null;
        $this->galponGranjaId = '';
        $this->galponNombre = '';
        $this->galponCodigo = '';
        $this->galponCapacidad = '';
        $this->galponEstado = GalponEstado::Activo->value;
        $this->galponActivo = true;
        $this->galponObservacion = '';
        $this->resetValidation();
    }

    private function resetLoteCrearFormulario(): void
    {
        $this->loteGalponId = '';
        $this->loteCodigoSma = '';
        $this->loteTipoHuevo = TipoHuevo::Blanco->value;
        $this->loteCantidad = '';
        $this->loteFechaNacimiento = now()->subWeeks(20)->format('Y-m-d');
        $this->resetValidation();
    }
}
