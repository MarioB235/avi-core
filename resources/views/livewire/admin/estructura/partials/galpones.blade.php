<div class="space-y-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="grid min-w-0 flex-1 gap-3 sm:grid-cols-2">
            <x-ui.select
                label="Granja"
                name="filtroGranjaId"
                wire:model.live="filtroGranjaId"
                placeholder="Todas las granjas"
                :options="$granjasOptions"
            />

            <x-ui.input
                label="Buscar"
                name="busqueda"
                wire:model.live.debounce.300ms="busqueda"
                placeholder="Nombre o código"
            />
        </div>

        @if ($canManageEstructura)
            <x-ui.button type="button" wire:click="abrirCrearGalpon" class="w-full shrink-0 lg:w-auto">
                <x-ui.icon name="plus" class="size-4" />
                Nuevo galpón
            </x-ui.button>
        @endif
    </div>

    <x-ui.card padding="none" class="overflow-hidden">
        @if ($galpones->isEmpty())
            <div class="p-8">
                <x-ui.empty-state
                    title="No hay galpones para mostrar"
                    description="Creá un galpón dentro de una granja activa."
                    icon="warehouse"
                />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="avicore-table min-w-[720px]">
                    <thead>
                        <tr>
                            <th scope="col">Galpón</th>
                            <th scope="col" class="hidden md:table-cell">Granja</th>
                            <th scope="col">Aves</th>
                            <th scope="col">Estado</th>
                            @if ($canManageEstructura)
                                <th scope="col" class="text-right"><span class="sr-only">Acciones</span></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($galpones as $galpon)
                            <tr wire:key="galpon-{{ $galpon->id }}" class="md:hover:bg-avicore-soft/60">
                                <td>
                                    <p class="font-medium text-avicore-text">{{ $galpon->nombre }}</p>
                                    @if ($galpon->codigo)
                                        <p class="text-xs text-avicore-muted">{{ $galpon->codigo }}</p>
                                    @endif
                                </td>
                                <td class="hidden md:table-cell text-avicore-muted">{{ $galpon->granja->nombre }}</td>
                                <td class="text-avicore-muted">{{ number_format($galpon->aves_actuales, 0, ',', '.') }}</td>
                                <td>
                                    <x-ui.badge variant="{{ $galpon->activo && $galpon->estado->permiteCarga() ? 'success' : 'neutral' }}">
                                        {{ $galpon->estado->label() }}
                                    </x-ui.badge>
                                </td>
                                @if ($canManageEstructura)
                                    <td class="text-right">
                                        <x-ui.button type="button" variant="ghost" size="sm" wire:click="abrirEditarGalpon({{ $galpon->id }})">
                                            Editar
                                        </x-ui.button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-avicore-border px-4 py-3">
                {{ $galpones->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
