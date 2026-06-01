<x-layouts.admin title="Panel administrativo" heading="Inicio" subheading="{{ auth()->user()->name }} · {{ auth()->user()->rol->label() }}">
    @if (session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.kpi-card label="Estado" value="MVP" hint="Autenticación activa" />
        <x-ui.kpi-card label="Producción hoy" value="—" hint="Próximo bloque" />
        <x-ui.kpi-card label="Galpones" value="—" hint="Próximo bloque" />
    </div>

    <x-ui.card title="Próximos módulos" class="mt-8">
        <p class="text-sm leading-relaxed text-avicore-muted">
            Dashboard en vivo, galpones y carga operario se implementan en los bloques siguientes del plan de desarrollo.
        </p>
    </x-ui.card>
</x-layouts.admin>
