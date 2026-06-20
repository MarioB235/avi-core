<div class="space-y-4">
    <x-ui.card>
        <h1 class="text-base font-medium text-avicore-text">Nueva carga</h1>
        <p class="mt-1 text-sm text-avicore-muted">
            Galpón:
            <span class="font-medium text-avicore-text">{{ $galponEtiqueta }}</span>
        </p>
        @unless ($galpon)
            <p class="mt-2 text-sm text-avicore-muted">
                Elegí un galpón en la pestaña Galpón antes de cargar datos.
            </p>
        @endunless
    </x-ui.card>

    <div class="grid grid-cols-2 gap-3">
        <x-ui.button
            href="{{ $galpon ? route('operario.carga.huevos') : route('operario.galpon') }}"
            class="min-h-[5.5rem] flex-col gap-2 py-4 text-base"
        >
            <x-ui.icon name="egg" class="size-6" />
            Huevos
        </x-ui.button>

        <x-ui.button variant="secondary" class="min-h-[5.5rem] flex-col gap-2 py-4 text-base" disabled>
            <x-ui.icon name="users" class="size-6 opacity-60" />
            <span>Muertes</span>
            <span class="text-xs font-normal text-avicore-muted">Próximamente</span>
        </x-ui.button>

        <x-ui.button variant="secondary" class="min-h-[5.5rem] flex-col gap-2 py-4 text-base" disabled>
            <x-ui.icon name="layers" class="size-6 opacity-60" />
            <span>Alimento</span>
            <span class="text-xs font-normal text-avicore-muted">Próximamente</span>
        </x-ui.button>

        <x-ui.button variant="secondary" class="min-h-[5.5rem] flex-col gap-2 py-4 text-base" disabled>
            <x-ui.icon name="clipboard-list" class="size-6 opacity-60" />
            <span>Combinada</span>
            <span class="text-xs font-normal text-avicore-muted">Próximamente</span>
        </x-ui.button>
    </div>
</div>
