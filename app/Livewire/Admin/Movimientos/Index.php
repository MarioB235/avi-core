<?php

namespace App\Livewire\Admin\Movimientos;

use App\Actions\Movimiento\RegistrarAjusteInventarioAvesAction;
use App\Actions\Movimiento\RegistrarCierreLoteAction;
use App\Actions\Movimiento\RegistrarEntradaAvesAction;
use App\Actions\Movimiento\RegistrarFaenaAction;
use App\Actions\Movimiento\RegistrarTrasladoAvesAction;
use App\Livewire\Concerns\RequiresAdminModuleAccess;
use App\Models\Galpon;
use App\Models\Lote;
use App\Models\MovimientoAves;
use App\Models\User;
use App\Services\AdminResumenService;
use App\Services\MovimientoAvesVistaPreviaService;
use App\Services\SoporteEmpresaService;
use App\Support\IdempotenciaCaptura;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Movimientos de aves · AviCore')]
class Index extends Component
{
    use RequiresAdminModuleAccess;

    public string $tipo = 'traslado';

    public string $galponOrigenId = '';

    public string $galponDestinoId = '';

    public string $galponId = '';

    public string $loteId = '';

    public string $cantidad = '';

    public string $conteoFisico = '';

    public bool $cerrarCicloLote = true;

    public string $motivo = '';

    public string $destinoFaena = '';

    public string $referenciaRemito = '';

    public bool $dialogConfirmarAbierto = false;

    protected function requiredAdminModuleAbility(): string
    {
        return 'admin.viewMovimientos';
    }

    public function mount(SoporteEmpresaService $soporte): void
    {
        $this->authorizeAdminModule();

        $user = auth()->user();

        if ($user instanceof User && $user->isAdminAvicore() && $soporte->isActive()) {
            $soporte->recordAccionForActiveSession('consulta_movimientos', [
                'ruta' => $soporte->entryRouteName(),
            ]);
        }
    }

    public function updatedTipo(): void
    {
        $this->resetValidation();
        $this->dialogConfirmarAbierto = false;
        $this->loteId = '';
    }

    public function updatedGalponOrigenId(): void
    {
        $this->loteId = '';
    }

    public function updatedGalponId(): void
    {
        $this->loteId = '';
    }

    public function abrirConfirmacion(): void
    {
        $this->validate($this->reglasFormulario());

        $preview = $this->vistaPrevia();

        if (! $preview['valido']) {
            throw ValidationException::withMessages([
                'cantidad' => $preview['errores'][0] ?? 'Revisá los datos del movimiento.',
            ]);
        }

        $this->dialogConfirmarAbierto = true;
    }

    public function ejecutarMovimiento(
        RegistrarTrasladoAvesAction $traslado,
        RegistrarEntradaAvesAction $entrada,
        RegistrarAjusteInventarioAvesAction $ajuste,
        RegistrarCierreLoteAction $cierre,
        RegistrarFaenaAction $faena,
    ): void {
        $this->validate($this->reglasFormulario());

        $preview = $this->vistaPrevia();

        if (! $preview['valido']) {
            throw ValidationException::withMessages([
                'cantidad' => $preview['errores'][0] ?? 'Revisá los datos del movimiento.',
            ]);
        }

        /** @var User $user */
        $user = auth()->user();
        Gate::forUser($user)->authorize('create', MovimientoAves::class);

        $motivo = trim($this->motivo);

        match ($this->tipo) {
            'traslado' => $traslado->execute(
                $user,
                $this->galponOrigen(),
                $this->galponDestino(),
                $this->lote(),
                (int) $this->cantidad,
                $motivo,
            ),
            'entrada' => $entrada->execute(
                $user,
                $this->galpon(),
                $this->lote(),
                (int) $this->cantidad,
                $motivo,
                IdempotenciaCaptura::generarClave(),
            ),
            'ajuste' => $ajuste->execute(
                $user,
                $this->galpon(),
                (int) $this->conteoFisico,
                $motivo,
            ),
            'cierre' => $cierre->execute(
                $user,
                $this->galpon(),
                $this->lote(),
                (int) $this->cantidad,
                $motivo,
                cerrarCicloLote: $this->cerrarCicloLote,
            ),
            'faena' => $faena->execute(
                $user,
                $this->galpon(),
                $this->lote(),
                (int) $this->cantidad,
                $motivo,
                trim($this->destinoFaena),
                $this->cerrarCicloLote,
                $this->referenciaRemito !== '' ? trim($this->referenciaRemito) : null,
            ),
            default => throw ValidationException::withMessages([
                'tipo' => 'Tipo de movimiento no válido.',
            ]),
        };

        $this->reset(['cantidad', 'conteoFisico', 'motivo', 'destinoFaena', 'referenciaRemito', 'dialogConfirmarAbierto']);
        session()->flash('status', 'movimiento-registrado');
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    public function vistaPrevia(): array
    {
        $vistaPrevia = app(MovimientoAvesVistaPreviaService::class);

        return match ($this->tipo) {
            'traslado' => $this->previewTraslado($vistaPrevia),
            'entrada' => $this->previewEntrada($vistaPrevia),
            'ajuste' => $this->previewAjuste($vistaPrevia),
            'cierre' => $this->previewCierre($vistaPrevia),
            'faena' => $this->previewFaena($vistaPrevia),
            default => [
                'valido' => false,
                'lineas' => [],
                'errores' => ['Elegí un tipo de movimiento.'],
                'conserva_total_empresa' => true,
            ],
        };
    }

    public function render(AdminResumenService $resumen): View
    {
        /** @var User $user */
        $user = auth()->user();

        $galpones = $resumen->galponesParaFiltro($user, null);
        $lotes = $this->lotesParaFormulario();

        $galponesOptions = $galpones
            ->mapWithKeys(fn (Galpon $galpon): array => [
                (string) $galpon->id => $galpon->nombre,
            ])
            ->all();

        return view('livewire.admin.movimientos.index', [
            'galponesOptions' => $galponesOptions,
            'lotesOptions' => $lotes,
            'tipoOptions' => [
                'traslado' => 'Traslado entre galpones',
                'entrada' => 'Entrada externa',
                'ajuste' => 'Ajuste de inventario',
                'cierre' => 'Cierre / salida de lote',
                'faena' => 'Salida a faena',
            ],
            'vistaPrevia' => $this->debeCalcularVistaPreviaEnRender()
                ? $this->vistaPrevia()
                : $this->previewIncompleta(),
        ]);
    }

    private function debeCalcularVistaPreviaEnRender(): bool
    {
        return match ($this->tipo) {
            'traslado' => $this->galponOrigenId !== ''
                || $this->galponDestinoId !== ''
                || $this->cantidad !== '',
            'entrada', 'cierre' => $this->galponId !== ''
                || $this->loteId !== ''
                || $this->cantidad !== '',
            'faena' => $this->galponId !== ''
                || $this->loteId !== ''
                || $this->cantidad !== ''
                || $this->destinoFaena !== '',
            'ajuste' => $this->galponId !== '' || $this->conteoFisico !== '',
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function reglasFormulario(): array
    {
        $reglas = [
            'tipo' => ['required', Rule::in(['traslado', 'entrada', 'ajuste', 'cierre', 'faena'])],
            'motivo' => ['required', 'string', 'min:3', 'max:500'],
        ];

        return match ($this->tipo) {
            'traslado' => array_merge($reglas, [
                'galponOrigenId' => ['required', 'integer'],
                'galponDestinoId' => ['required', 'integer', 'different:galponOrigenId'],
                'loteId' => ['required', 'integer'],
                'cantidad' => ['required', 'integer', 'min:1'],
            ]),
            'entrada' => array_merge($reglas, [
                'galponId' => ['required', 'integer'],
                'loteId' => ['required', 'integer'],
                'cantidad' => ['required', 'integer', 'min:1'],
            ]),
            'ajuste' => array_merge($reglas, [
                'galponId' => ['required', 'integer'],
                'conteoFisico' => ['required', 'integer', 'min:0'],
            ]),
            'cierre' => array_merge($reglas, [
                'galponId' => ['required', 'integer'],
                'loteId' => ['required', 'integer'],
                'cantidad' => ['required', 'integer', 'min:1'],
            ]),
            'faena' => array_merge($reglas, [
                'galponId' => ['required', 'integer'],
                'loteId' => ['required', 'integer'],
                'cantidad' => ['required', 'integer', 'min:1'],
                'destinoFaena' => ['required', 'string', 'min:3', 'max:200'],
                'referenciaRemito' => ['nullable', 'string', 'max:120'],
            ]),
            default => $reglas,
        };
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    private function previewTraslado(MovimientoAvesVistaPreviaService $service): array
    {
        if ($this->galponOrigenId === '' || $this->galponDestinoId === '' || $this->cantidad === '') {
            return $this->previewIncompleta();
        }

        return $service->traslado(
            $this->galponOrigen(),
            $this->galponDestino(),
            (int) $this->cantidad,
        );
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    private function previewEntrada(MovimientoAvesVistaPreviaService $service): array
    {
        if ($this->galponId === '' || $this->cantidad === '') {
            return $this->previewIncompleta();
        }

        return $service->entradaExterna($this->galpon(), (int) $this->cantidad);
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    private function previewAjuste(MovimientoAvesVistaPreviaService $service): array
    {
        if ($this->galponId === '' || $this->conteoFisico === '') {
            return $this->previewIncompleta();
        }

        return $service->ajusteInventario($this->galpon(), (int) $this->conteoFisico);
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    private function previewCierre(MovimientoAvesVistaPreviaService $service): array
    {
        if ($this->galponId === '' || $this->loteId === '' || $this->cantidad === '') {
            return $this->previewIncompleta();
        }

        return $service->cierreLote(
            $this->galpon(),
            $this->lote(),
            (int) $this->cantidad,
            $this->cerrarCicloLote,
        );
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    private function previewFaena(MovimientoAvesVistaPreviaService $service): array
    {
        if ($this->galponId === '' || $this->loteId === '' || $this->cantidad === '' || $this->destinoFaena === '') {
            return $this->previewIncompleta();
        }

        return $service->faena(
            $this->galpon(),
            $this->lote(),
            (int) $this->cantidad,
            $this->destinoFaena,
            $this->cerrarCicloLote,
            $this->referenciaRemito,
        );
    }

    /**
     * @return array{valido: bool, lineas: list<string>, errores: list<string>, conserva_total_empresa: bool}
     */
    private function previewIncompleta(): array
    {
        return [
            'valido' => false,
            'lineas' => ['Completá los campos para ver el efecto estimado.'],
            'errores' => [],
            'conserva_total_empresa' => true,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function lotesParaFormulario(): array
    {
        $galponId = match ($this->tipo) {
            'traslado' => $this->galponOrigenId !== '' ? (int) $this->galponOrigenId : null,
            'entrada', 'cierre', 'faena' => $this->galponId !== '' ? (int) $this->galponId : null,
            default => null,
        };

        if ($galponId === null) {
            return [];
        }

        $galpon = Galpon::query()->find($galponId);

        if (! $galpon instanceof Galpon) {
            return [];
        }

        /** @var User $user */
        $user = auth()->user();
        if ((int) $galpon->empresa_id !== (int) $user->empresa_id) {
            return [];
        }

        return app(MovimientoAvesVistaPreviaService::class)
            ->lotesActivosEnGalpon($galpon)
            ->mapWithKeys(fn (Lote $lote): array => [
                (string) $lote->id => $lote->codigo.' ('.number_format($lote->cantidad_inicial, 0, ',', '.').' inicial)',
            ])
            ->all();
    }

    private function galponOrigen(): Galpon
    {
        return $this->resolveGalpon((int) $this->galponOrigenId);
    }

    private function galponDestino(): Galpon
    {
        return $this->resolveGalpon((int) $this->galponDestinoId);
    }

    private function galpon(): Galpon
    {
        return $this->resolveGalpon((int) $this->galponId);
    }

    private function lote(): Lote
    {
        /** @var User $user */
        $user = auth()->user();

        return Lote::query()
            ->whereKey((int) $this->loteId)
            ->where('empresa_id', $user->empresa_id)
            ->firstOrFail();
    }

    private function resolveGalpon(int $galponId): Galpon
    {
        /** @var User $user */
        $user = auth()->user();

        return Galpon::query()
            ->whereKey($galponId)
            ->whereHas('granja', fn ($q) => $q->where('empresa_id', $user->empresa_id))
            ->firstOrFail();
    }
}
