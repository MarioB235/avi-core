<?php

namespace App\Livewire\Admin\Estructura;

use App\Actions\Galpon\CreateGalponAction;
use App\Actions\Galpon\UpdateGalponAction;
use App\Actions\Granja\CreateGranjaAction;
use App\Actions\Granja\UpdateGranjaAction;
use App\Actions\Lote\RegistrarLoteAction;
use App\Actions\Lote\UpdateLoteAction;
use App\Enums\GalponEstado;
use App\Enums\LoteEstado;
use App\Enums\TipoHuevo;
use App\Models\Galpon;
use App\Models\Granja;
use App\Models\Lote;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
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
    use WithPagination;

    #[Url(as: 'seccion', except: 'granjas', history: true)]
    public string $seccion = 'granjas';

    public string $busqueda = '';

    public string $filtroGranjaId = '';

    public string $filtroGalponId = '';

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

    public string $loteEstado = '';

    public string $loteLineaRaza = '';

    public string $loteObservacion = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Granja::class);

        if (! in_array($this->seccion, ['granjas', 'galpones', 'lotes'], true)) {
            $this->seccion = 'granjas';
        }
    }

    public function updatedSeccion(): void
    {
        $this->resetPage();
        $this->busqueda = '';
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

        if ($this->editingGranjaId !== null) {
            $granja = $this->findScopedGranja($this->editingGranjaId);
            $updateGranja->execute(auth()->user(), $granja, $payload);
            $mensaje = 'Granja actualizada.';
        } else {
            $createGranja->execute(auth()->user(), $payload);
            $mensaje = 'Granja creada.';
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

        if ($this->editingGalponId !== null) {
            $galpon = $this->findScopedGalpon($this->editingGalponId);
            $updateGalpon->execute(auth()->user(), $galpon, $payload);
            $mensaje = 'Galpón actualizado.';
        } else {
            $createGalpon->execute(auth()->user(), $payload);
            $mensaje = 'Galpón creado.';
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
        $galpon = $this->findScopedGalpon((int) $this->loteGalponId);
        $tipo = TipoHuevo::from($this->loteTipoHuevo);

        $lotes = $registrarLote->execute(
            auth()->user(),
            $galpon,
            [$tipo->value => (int) $this->loteCantidad],
            Carbon::parse($this->loteFechaNacimiento),
            codigoSma: $this->loteCodigoSma !== '' ? $this->loteCodigoSma : null,
        );

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
        $this->loteEstado = $lote->estado->value;
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
        $this->loteEstado = '';
        $this->loteObservacion = '';
        $this->resetValidation();
    }

    public function guardarLoteEditar(UpdateLoteAction $updateLote): void
    {
        $lote = $this->findScopedLote((int) $this->editingLoteId);

        $updateLote->execute(auth()->user(), $lote, [
            'codigo_sma' => $this->loteCodigoSma !== '' ? $this->loteCodigoSma : null,
            'linea_raza' => $this->loteLineaRaza !== '' ? $this->loteLineaRaza : null,
            'estado' => $this->loteEstado,
            'observacion' => $this->loteObservacion !== '' ? $this->loteObservacion : null,
        ]);

        $this->cerrarLoteEditar();
        $this->dispatch('snackbar-show', message: 'Lote actualizado.', variant: 'success');
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

        return view('livewire.admin.estructura.index', [
            'actor' => $actor,
            'canManageEstructura' => $actor->rol->canManageEstructura(),
            'canManageLotes' => Gate::forUser($actor)->allows('create', Lote::class),
            'granjas' => $this->seccion === 'granjas' ? $this->granjasQuery()->paginate(15) : null,
            'galpones' => $this->seccion === 'galpones' ? $this->galponesQuery()->paginate(15) : null,
            'lotes' => $this->seccion === 'lotes' ? $this->lotesQuery()->paginate(15) : null,
            'granjasOptions' => $granjasOptions,
            'galponesOptions' => $galponesOptions,
            'galponEstadoOptions' => GalponEstado::options(),
            'loteEstadoOptions' => collect(LoteEstado::cases())
                ->mapWithKeys(fn (LoteEstado $estado): array => [$estado->value => $estado->label()])
                ->all(),
            'tipoHuevoOptions' => TipoHuevo::optionsUi(),
        ]);
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
            ->orderBy('nombre');
    }

    private function galponesQuery()
    {
        $actor = auth()->user();

        return Galpon::query()
            ->with('granja')
            ->when($actor->empresa_id !== null, fn ($q) => $q->where('empresa_id', $actor->empresa_id))
            ->when($this->filtroGranjaId !== '', fn ($q) => $q->where('granja_id', (int) $this->filtroGranjaId))
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
        $query = Granja::query()->whereKey($granjaId);

        if ($actor->empresa_id !== null) {
            $query->where('empresa_id', $actor->empresa_id);
        }

        $granja = $query->firstOrFail();
        $this->authorize('view', $granja);

        return $granja;
    }

    private function findScopedGalpon(int $galponId): Galpon
    {
        $actor = auth()->user();
        $query = Galpon::query()->whereKey($galponId);

        if ($actor->empresa_id !== null) {
            $query->where('empresa_id', $actor->empresa_id);
        }

        $galpon = $query->firstOrFail();
        $this->authorize('view', $galpon);

        return $galpon;
    }

    private function findScopedLote(int $loteId): Lote
    {
        $actor = auth()->user();
        $query = Lote::query()->whereKey($loteId);

        if ($actor->empresa_id !== null) {
            $query->where('empresa_id', $actor->empresa_id);
        }

        $lote = $query->firstOrFail();
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
