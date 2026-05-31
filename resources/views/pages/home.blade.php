<x-layouts.public :title="config('app.name')">
    <div class="w-full max-w-lg space-y-8 text-center">
        <x-ui.logo subtitle="Gestión operativa avícola" class="justify-center" />

        <x-ui.card>
            <p class="text-sm text-avicore-muted">
                Base del proyecto lista. El stack principal está configurado: Laravel, Livewire, Tailwind, Alpine (vía Livewire) y PostgreSQL.
            </p>
        </x-ui.card>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
            <x-ui.button href="{{ route('dev.admin-layout') }}" class="w-full sm:w-auto">
                Ver layout admin
            </x-ui.button>
            <x-ui.button href="{{ route('dev.operario-layout') }}" variant="secondary" class="w-full sm:w-auto">
                Ver layout operario
            </x-ui.button>
        </div>

        <p class="text-xs text-avicore-muted">
            Próximo módulo: login y cambio obligatorio de contraseña.
        </p>
    </div>
</x-layouts.public>
