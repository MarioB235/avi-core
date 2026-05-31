<x-layouts.operario-mobile title="Operario">
    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card>
        <p class="text-sm text-avicore-muted">
            Hola, <strong>{{ auth()->user()->name }}</strong>. La vista móvil de carga operativa llega en el Bloque 5 del plan.
        </p>
    </x-ui.card>

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <x-ui.button type="submit" variant="secondary" class="w-full">Cerrar sesión</x-ui.button>
    </form>
</x-layouts.operario-mobile>
