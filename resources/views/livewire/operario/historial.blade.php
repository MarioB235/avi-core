<div class="space-y-4">
    <x-ui.card>
        <h1 class="text-base font-medium text-avicore-text">Historial de hoy</h1>
        <p class="mt-1 text-sm text-avicore-muted">
            Galpón actual:
            <span class="font-medium text-avicore-text">{{ $galponEtiqueta }}</span>
        </p>
    </x-ui.card>

    <x-ui.card>
        @if ($ultimasCargas->isEmpty())
            <x-ui.empty-state
                title="Sin cargas hoy"
                description="Cuando registres huevos u otros datos, aparecerán acá."
                icon="clock"
            />
        @else
            <ul class="space-y-3">
                @foreach ($ultimasCargas as $carga)
                    <li class="rounded-lg border border-avicore-border px-3 py-2">
                        <p class="text-sm font-medium text-avicore-text">
                            {{ $carga->tipo->label() }} · {{ $carga->cantidadResumen() }}
                        </p>
                        <p class="text-xs text-avicore-muted">
                            {{ $carga->galpon?->displayName() }} · {{ $carga->created_at->format('H:i') }}
                        </p>
                        @if ($carga->observacion)
                            <p class="mt-1 text-xs text-avicore-muted">{{ $carga->observacion }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    <x-ui.card>
        <h2 class="text-sm font-medium text-avicore-text">Tu cuenta</h2>
        <p class="mt-2 text-sm text-avicore-text">{{ auth()->user()->name }}</p>
        <p class="text-xs text-avicore-muted">{{ auth()->user()->documento }}</p>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <x-ui.button type="submit" variant="secondary" class="w-full">
                Cerrar sesión
            </x-ui.button>
        </form>
    </x-ui.card>
</div>
