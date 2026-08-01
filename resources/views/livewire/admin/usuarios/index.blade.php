<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Usuarios"
        subtitle="Gestioná el equipo y los roles de acceso."
    />

    <div class="avicore-operario-home-sheet space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0 flex-1 space-y-4">
            <div class="grid gap-3 sm:grid-cols-3">
                <x-ui.input
                    label="Buscar"
                    name="busqueda"
                    wire:model.live.debounce.300ms="busqueda"
                    placeholder="Nombre, documento o correo"
                />

                <x-ui.select
                    label="Rol"
                    name="filtroRol"
                    wire:model.live="filtroRol"
                    placeholder="Todos los roles"
                    :options="$filtroRolOptions"
                />

                <x-ui.select
                    label="Estado"
                    name="filtroEstado"
                    wire:model.live="filtroEstado"
                    :options="[
                        'todos' => 'Todos',
                        'activos' => 'Activos',
                        'inactivos' => 'Inactivos',
                    ]"
                />
            </div>
        </div>

        @if ($canCreate)
            <x-ui.button type="button" wire:click="abrirCrear" class="w-full shrink-0 sm:w-auto">
                <x-ui.icon name="plus" class="size-4" />
                Nuevo usuario
            </x-ui.button>
        @endif
    </div>

    <x-ui.card padding="none" class="overflow-hidden">
        @if ($users->isEmpty())
            <div class="p-8">
                <x-ui.empty-state
                    title="No hay usuarios para mostrar"
                    description="Ajustá los filtros o creá el primer usuario del equipo."
                    icon="users"
                />
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="avicore-table min-w-[640px]">
                    <thead>
                        <tr>
                            <th scope="col">Usuario</th>
                            <th scope="col" class="hidden md:table-cell">Documento</th>
                            <th scope="col">Rol</th>
                            @if ($actor->isAdminAvicore())
                                <th scope="col" class="hidden lg:table-cell">Empresa</th>
                            @endif
                            <th scope="col">Estado</th>
                            <th scope="col" class="text-right">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="md:hover:bg-avicore-soft/60">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-ui.user-avatar :name="$user->name" size="sm" decorative />
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-avicore-text">{{ $user->name }}</p>
                                            @if ($user->email)
                                                <p class="truncate text-xs text-avicore-muted">{{ $user->email }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="hidden md:table-cell text-avicore-muted">{{ $user->documento }}</td>
                                <td>
                                    <x-ui.badge variant="primary">{{ $user->rol->label() }}</x-ui.badge>
                                </td>
                                @if ($actor->isAdminAvicore())
                                    <td class="hidden lg:table-cell text-avicore-muted">
                                        {{ $user->empresa?->nombre ?? '—' }}
                                    </td>
                                @endif
                                <td>
                                    @if ($user->activo)
                                        <x-ui.badge variant="success">Activo</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="neutral">Inactivo</x-ui.badge>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap items-center justify-end gap-1">
                                        @can('update', $user)
                                            <x-ui.button
                                                type="button"
                                                variant="ghost"
                                                class="min-h-10 px-2.5 py-1.5 text-xs"
                                                wire:click="abrirEditar({{ $user->id }})"
                                            >
                                                Editar
                                            </x-ui.button>
                                        @endcan

                                        @can('resetPassword', $user)
                                            <x-ui.button
                                                type="button"
                                                variant="ghost"
                                                class="min-h-10 px-2.5 py-1.5 text-xs"
                                                wire:click="resetearPassword({{ $user->id }})"
                                                wire:confirm="¿Generar una contraseña temporal nueva para {{ $user->name }}?"
                                            >
                                                Reset clave
                                            </x-ui.button>
                                        @endcan

                                        @can('toggleActive', $user)
                                            <x-ui.button
                                                type="button"
                                                variant="ghost"
                                                class="min-h-10 px-2.5 py-1.5 text-xs"
                                                wire:click="toggleActivo({{ $user->id }})"
                                            >
                                                {{ $user->activo ? 'Desactivar' : 'Activar' }}
                                            </x-ui.button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-avicore-border px-4 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>

    <x-ui.dialog wire:model="dialogFormularioAbierto" :title="$editingUserId ? 'Editar usuario' : 'Nuevo usuario'">
        <form wire:submit="guardar" class="space-y-4">
            <x-ui.input
                label="Nombre completo"
                name="name"
                wire:model="name"
                placeholder="Ejemplo: Ana Pérez"
                required
                :error="$errors->first('name')"
            />

            <x-ui.input
                label="Documento"
                name="documento"
                wire:model="documento"
                placeholder="Sin puntos ni guiones"
                icon="id-card"
                required
                :error="$errors->first('documento')"
            />

            <x-ui.input
                label="Correo (opcional)"
                name="email"
                type="email"
                wire:model="email"
                placeholder="nombre@empresa.com"
                :error="$errors->first('email')"
            />

            <x-ui.select
                label="Rol"
                name="rol"
                wire:model="rol"
                :options="$roleOptions"
                :error="$errors->first('rol')"
            />

            @if ($actor->isAdminAvicore() && $editingUserId === null)
                <x-ui.select
                    label="Empresa"
                    name="empresa_id"
                    wire:model="empresa_id"
                    placeholder="Elegí una empresa"
                    :options="$empresas->mapWithKeys(fn ($empresa) => [$empresa->id => $empresa->nombre])->all()"
                    :hint="$rol === 'admin_avicore' ? 'No aplica para Admin AviCore.' : null"
                    :error="$errors->first('empresa_id')"
                />
            @endif

            @if ($editingUserId !== null)
                <label class="flex items-center gap-3 rounded-lg border border-avicore-border px-3 py-3">
                    <input
                        type="checkbox"
                        wire:model="activo"
                        class="size-4 rounded border-avicore-border-strong text-avicore-primary focus:ring-avicore-primary"
                    />
                    <span class="text-sm text-avicore-text">Usuario activo</span>
                </label>
                @error('activo')
                    <p class="text-sm text-avicore-danger" role="alert">{{ $message }}</p>
                @enderror
            @endif

            <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                <x-ui.button type="button" variant="secondary" wire:click="cerrarFormulario">
                    Cancelar
                </x-ui.button>
                <x-ui.button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="guardar"
                >
                    <span wire:loading.remove wire:target="guardar">Guardar</span>
                    <span wire:loading wire:target="guardar">Guardando…</span>
                </x-ui.button>
            </div>
        </form>
    </x-ui.dialog>

    <x-ui.dialog wire:model="dialogPasswordAbierto" title="Contraseña temporal">
        <div class="space-y-4">
            <p class="text-sm leading-relaxed text-avicore-muted">
                Copiá y entregá esta clave a <strong class="text-avicore-text">{{ $passwordUserName }}</strong>.
                Al ingresar deberá cambiarla. No se volverá a mostrar.
            </p>

            <div class="rounded-lg border border-avicore-border bg-avicore-surface px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-avicore-muted">Contraseña</p>
                <p class="mt-1 break-all font-mono text-lg font-semibold text-avicore-text" data-testid="plain-password">
                    {{ $plainPassword }}
                </p>
            </div>

            <div class="flex justify-end">
                <x-ui.button type="button" wire:click="cerrarPassword">
                    Entendido
                </x-ui.button>
            </div>
        </div>
    </x-ui.dialog>
    </div>
</div>
