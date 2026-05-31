<x-layouts.admin title="Panel administrativo">
    @if (session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card>
        <p class="text-sm text-avicore-muted">
            Sesión iniciada como <strong>{{ auth()->user()->name }}</strong>
            ({{ auth()->user()->rol->label() }}).
        </p>
        <p class="mt-3 text-sm text-avicore-muted">
            El dashboard y los módulos de gestión se implementan en los siguientes bloques del plan.
        </p>
    </x-ui.card>

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <x-ui.button type="submit" variant="secondary">Cerrar sesión</x-ui.button>
    </form>
</x-layouts.admin>
