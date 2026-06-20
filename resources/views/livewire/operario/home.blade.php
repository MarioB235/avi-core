<div class="space-y-4">
    @if (session('status'))
        <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card>
        <p class="text-sm text-avicore-muted">
            Hola, <span class="font-medium text-avicore-text">{{ auth()->user()->name }}</span>.
        </p>
        @unless ($galpon)
            <p class="mt-2 text-sm text-avicore-muted">
                Elegí un galpón en la pestaña Galpón para empezar a cargar.
            </p>
        @endunless
    </x-ui.card>

    <x-ui.card>
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-sm font-medium text-avicore-text">Últimas cargas de hoy</h2>
            @if ($ultimasCargas->isNotEmpty())
                <a
                    href="{{ route('operario.historial') }}"
                    class="text-xs font-medium text-avicore-primary underline-offset-2 hover:underline"
                >
                    Ver todo
                </a>
            @endif
        </div>

        @if ($ultimasCargas->isEmpty())
            <p class="mt-3 text-sm text-avicore-muted">Todavía no hay cargas registradas hoy.</p>
        @else
            <ul class="mt-3 space-y-3">
                @foreach ($ultimasCargas as $carga)
                    <li class="rounded-lg border border-avicore-border px-3 py-2">
                        <p class="text-sm font-medium text-avicore-text">
                            {{ $carga->tipo->label() }} · {{ $carga->cantidadResumen() }}
                        </p>
                        <p class="text-xs text-avicore-muted">
                            {{ $carga->galpon?->displayName() }} · {{ $carga->created_at->format('H:i') }}
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>
</div>
