<x-layouts.operario-mobile title="Operario" galpon="Galpón demo">
    @if (session('status'))
        <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card>
        <p class="text-sm leading-relaxed text-avicore-muted">
            Hola, <strong class="text-avicore-text">{{ auth()->user()->name }}</strong>.
            La carga operativa en campo (huevos, muertes, alimento) llega en el Bloque 5 del plan.
        </p>
    </x-ui.card>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-ui.button type="submit" variant="secondary" class="w-full">Cerrar sesión</x-ui.button>
    </form>
</x-layouts.operario-mobile>
