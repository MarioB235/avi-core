<x-layouts.public :title="config('app.name')">
    <div class="w-full max-w-lg space-y-8 text-center">
        <x-ui.logo subtitle="Gestión operativa avícola" class="justify-center" />

        <x-ui.card>
            <p class="text-sm text-avicore-muted">
                Ingresá con documento y contraseña. Los usuarios de prueba se cargan con
                <code class="text-xs">php artisan db:seed</code> (ver documentación de arranque local).
            </p>
        </x-ui.card>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
            <x-ui.button href="{{ route('login') }}" class="w-full sm:w-auto">
                Iniciar sesión
            </x-ui.button>
            @auth
                <x-ui.button href="{{ route(auth()->user()->homeRouteName()) }}" variant="secondary" class="w-full sm:w-auto">
                    Ir a mi panel
                </x-ui.button>
            @endauth
        </div>
    </div>
</x-layouts.public>
