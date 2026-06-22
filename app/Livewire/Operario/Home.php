<?php

namespace App\Livewire\Operario;

use App\Enums\GalponEstado;
use App\Models\Galpon;
use App\Models\User;
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

    public function render(OperarioGalponService $operarioGalponService): View
    {
        $user = auth()->user();
        $galpon = $operarioGalponService->galponActual($user);
        $hora = now()->hour;

        /** @var Collection<int, Galpon> $galpones */
        $galpones = $operarioGalponService->galponesDisponibles($user);

        return view('livewire.operario.home', [
            'galpon' => $galpon,
            'galpones' => $galpones,
            'galponEtiqueta' => $operarioGalponService->etiquetaGalpon($galpon),
            'ultimasCargas' => $operarioGalponService->ultimasCargasDelDia($user)->take(3),
            'cargasCompletadasHoy' => $operarioGalponService->cantidadCargasDelDia($user),
            'maplesProducidosHoy' => $operarioGalponService->maplesProducidosHoy($user),
            'saludo' => match (true) {
                $hora < 12 => 'Buenos días',
                $hora < 19 => 'Buenas tardes',
                default => 'Buenas noches',
            },
            'primerNombre' => self::primerNombreUsuario($user),
        ]);
    }

    private static function primerNombreUsuario(User $user): string
    {
        $nombre = trim($user->name);
        $partes = explode(' ', $nombre);

        return $partes[0] !== '' ? $partes[0] : $nombre;
    }
}
