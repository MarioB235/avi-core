<?php

namespace App\Livewire\Operario;

use App\Enums\GalponEstado;
use App\Models\Galpon;
use App\Models\Lote;
use App\Services\OperarioGalponResumenService;
use App\Services\OperarioGalponService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.operario-mobile')]
#[Title('Operario')]
class Home extends Component
{
    public ?int $galponId = null;

    public bool $selectorGalponAbierto = false;

    public function mount(OperarioGalponService $operarioGalponService): void
    {
        $galpon = $operarioGalponService->galponActual(auth()->user());
        $this->galponId = $galpon?->id;

        if (request()->boolean('abrir_galpon') || session('abrirSelectorGalpon')) {
            $this->selectorGalponAbierto = true;
            session()->forget('abrirSelectorGalpon');
        }
    }

    public function toggleSelectorGalpon(): void
    {
        $this->selectorGalponAbierto = ! $this->selectorGalponAbierto;
    }

    public function cerrarSelectorGalpon(): void
    {
        $this->selectorGalponAbierto = false;
    }

    public function seleccionarGalpon(int $galponId, OperarioGalponService $operarioGalponService): void
    {
        $this->galponId = $galponId;

        $user = auth()->user();

        $this->validate([
            'galponId' => [
                'required',
                'integer',
                Rule::exists('galpones', 'id')->where(function ($query) use ($user) {
                    $query->where('empresa_id', $user->empresa_id)
                        ->where('activo', true)
                        ->where('estado', GalponEstado::Activo->value);
                }),
            ],
        ], [
            'galponId.required' => 'Elegí un galpón para continuar.',
            'galponId.exists' => 'El galpón seleccionado no está disponible para carga.',
        ]);

        $galpon = $operarioGalponService->galponDisponibleParaUsuario($user, $this->galponId);

        if ($galpon === null) {
            throw ValidationException::withMessages([
                'galponId' => 'El galpón seleccionado no está disponible para carga.',
            ]);
        }

        $operarioGalponService->seleccionarGalpon($user, $galpon);

        $this->selectorGalponAbierto = false;

        $this->dispatch('snackbar-show', message: 'Galpón actualizado.', variant: 'success');
    }

    public function render(
        OperarioGalponService $operarioGalponService,
        OperarioGalponResumenService $operarioGalponResumenService,
    ): View {
        $user = auth()->user();
        $galpon = $operarioGalponService->galponActual($user);
        $hora = now()->hour;

        /** @var Collection<int, Galpon> $galpones */
        $galpones = $operarioGalponService->galponesDisponibles($user);
        $resumen = $galpon !== null ? $operarioGalponResumenService->resumen($galpon) : null;

        /** @var Collection<int, int> $edadSemanasPorLote */
        $edadSemanasPorLote = $resumen !== null
            ? $resumen['lotes']->mapWithKeys(
                fn (Lote $lote): array => [$lote->id => $operarioGalponResumenService->edadSemanas($lote)]
            )
            : collect();

        return view('livewire.operario.home', [
            'galpon' => $galpon,
            'galpones' => $galpones,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
            'resumen' => $resumen,
            'edadSemanasPorLote' => $edadSemanasPorLote,
            'saludo' => match (true) {
                $hora < 12 => 'Buenos días',
                $hora < 19 => 'Buenas tardes',
                default => 'Buenas noches',
            },
        ]);
    }
}
