<x-layouts.admin title="Panel administrativo" heading="Inicio" subheading="Resumen de tu sesión y accesos próximos">
    @if (session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.card title="Sesión">
            <p class="text-sm text-avicore-muted">
                Conectado como <strong class="text-avicore-text">{{ auth()->user()->name }}</strong>
            </p>
            <p class="mt-2">
                <x-ui.badge variant="primary">{{ auth()->user()->rol->label() }}</x-ui.badge>
            </p>
        </x-ui.card>

        <x-ui.card title="Próximos módulos">
            <p class="text-sm leading-relaxed text-avicore-muted">
                El dashboard en vivo y la gestión de galpones se implementan en los bloques siguientes del plan de desarrollo.
            </p>
        </x-ui.card>

        <x-ui.card title="Estado" class="sm:col-span-2 lg:col-span-1">
            <p class="avicore-kpi-value">MVP</p>
            <p class="mt-1 text-sm text-avicore-muted">Bloque 2 — autenticación activa</p>
        </x-ui.card>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-8">
        @csrf
        <x-ui.button type="submit" variant="secondary">Cerrar sesión</x-ui.button>
    </form>
</x-layouts.admin>
