<x-layouts.operario-mobile title="Operario">
    @if (session('status'))
        <x-ui.alert variant="success">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card>
        <p class="text-sm text-avicore-muted">
            Hola, <span class="font-medium text-avicore-text">{{ auth()->user()->name }}</span>.
            La carga en campo llega en el Bloque 5.
        </p>
    </x-ui.card>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-ui.button type="submit" variant="secondary" class="w-full">Cerrar sesión</x-ui.button>
    </form>
</x-layouts.operario-mobile>
