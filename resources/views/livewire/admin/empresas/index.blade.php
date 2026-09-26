<div class="avicore-operario-home">
    <x-admin.page-hero
        title="Empresas"
        subtitle="Alta, suspensión y reactivación de clientes."
    />

    <div class="avicore-operario-home-sheet space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0 flex-1">
                <x-ui.input
                    label="Buscar"
                    name="busqueda"
                    wire:model.live.debounce.300ms="busqueda"
                    placeholder="Nombre o identificador"
                />
            </div>

            @if ($canCreate)
                <x-ui.button type="button" wire:click="abrirCrear" class="w-full shrink-0 sm:w-auto">
                    <x-ui.icon name="plus" class="size-4" />
                    Nueva empresa
                </x-ui.button>
            @endif
        </div>

        <x-ui.card padding="none" class="overflow-hidden">
            @if ($empresas->isEmpty())
                <div class="p-8">
                    <x-ui.empty-state
                        title="No hay empresas para mostrar"
                        description="Creá la primera empresa cliente sin depender del seed demo."
                        icon="layers"
                    />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="avicore-table min-w-[640px]">
                        <thead>
                            <tr>
                                <th scope="col">Empresa</th>
                                <th scope="col" class="hidden md:table-cell">Identificador</th>
                                <th scope="col">Estado</th>
                                <th scope="col" class="hidden lg:table-cell">Usuarios</th>
                                <th scope="col" class="text-right">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($empresas as $empresa)
                                <tr wire:key="empresa-{{ $empresa->id }}" class="md:hover:bg-avicore-soft/60">
                                    <td>
                                        <p class="font-medium text-avicore-text">{{ $empresa->nombre }}</p>
                                    </td>
                                    <td class="hidden md:table-cell font-mono text-sm text-avicore-muted">
                                        {{ $empresa->codigo }}
                                    </td>
                                    <td>
                                        @if ($empresa->estado === \App\Enums\EmpresaEstado::Activa)
                                            <x-ui.badge variant="success">{{ $empresa->estado->label() }}</x-ui.badge>
                                        @elseif ($empresa->estado === \App\Enums\EmpresaEstado::Suspendida)
                                            <x-ui.badge variant="warning">{{ $empresa->estado->label() }}</x-ui.badge>
                                        @else
                                            <x-ui.badge variant="neutral">{{ $empresa->estado->label() }}</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="hidden lg:table-cell text-avicore-muted">
                                        {{ $empresa->users_count }}
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap items-center justify-end gap-1">
                                            @can('update', $empresa)
                                                <x-ui.button
                                                    type="button"
                                                    variant="ghost"
                                                    class="min-h-10 px-2.5 py-1.5 text-xs"
                                                    wire:click="abrirConfigurar({{ $empresa->id }})"
                                                >
                                                    Configurar
                                                </x-ui.button>
                                            @endcan

                                            @can('updateEstado', $empresa)
                                                <x-ui.button
                                                    type="button"
                                                    variant="ghost"
                                                    class="min-h-10 px-2.5 py-1.5 text-xs"
                                                    wire:click="abrirCambioEstado({{ $empresa->id }})"
                                                >
                                                    Cambiar estado
                                                </x-ui.button>
                                            @endcan

                                            @can('enterSupport', $empresa)
                                                <x-ui.button
                                                    type="button"
                                                    variant="ghost"
                                                    class="min-h-10 px-2.5 py-1.5 text-xs"
                                                    wire:click="abrirSoporte({{ $empresa->id }})"
                                                >
                                                    Soporte
                                                </x-ui.button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($empresas->hasPages())
                    <div class="border-t border-avicore-border px-4 py-3">
                        {{ $empresas->links() }}
                    </div>
                @endif
            @endif
        </x-ui.card>

        <x-ui.dialog wire:model="dialogFormularioAbierto" title="Nueva empresa">
            <form wire:submit="guardar" class="space-y-4">
                <x-ui.input
                    label="Nombre de la empresa"
                    name="nombre"
                    wire:model="nombre"
                    placeholder="Ejemplo: Avícola del Sur"
                    required
                    :error="$errors->first('nombre')"
                />

                <x-ui.input
                    label="Identificador"
                    name="codigo"
                    wire:model="codigo"
                    placeholder="Ejemplo: ADSUR"
                    hint="Código único; se guarda en mayúsculas."
                    required
                    :error="$errors->first('codigo')"
                />

                <x-ui.select
                    label="Estado inicial"
                    name="estado"
                    wire:model="estado"
                    :options="$estadoOptions"
                    :error="$errors->first('estado')"
                />

                <div class="rounded-lg border border-avicore-border bg-avicore-surface px-4 py-3">
                    <p class="text-sm font-medium text-avicore-text">Dueño inicial</p>
                    <p class="mt-1 text-xs text-avicore-muted">
                        Se crea un usuario Dueño con contraseña temporal para operar la empresa vacía.
                    </p>
                </div>

                <x-ui.input
                    label="Nombre del dueño"
                    name="admin_name"
                    wire:model="admin_name"
                    placeholder="Ejemplo: Juan Pérez"
                    required
                    :error="$errors->first('admin_name')"
                />

                <x-ui.input
                    label="Documento del dueño"
                    name="admin_documento"
                    wire:model="admin_documento"
                    placeholder="Sin puntos ni guiones"
                    icon="id-card"
                    required
                    :error="$errors->first('admin_documento')"
                />

                <x-ui.input
                    label="Correo del dueño (opcional)"
                    name="admin_email"
                    type="email"
                    wire:model="admin_email"
                    placeholder="nombre@empresa.com"
                    :error="$errors->first('admin_email')"
                />

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <x-ui.button type="button" variant="secondary" wire:click="cerrarFormulario">
                        Cancelar
                    </x-ui.button>
                    <x-ui.button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="guardar"
                    >
                        <span wire:loading.remove wire:target="guardar">Crear empresa</span>
                        <span wire:loading wire:target="guardar">Creando…</span>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.dialog>

        <x-ui.dialog
            wire:model="dialogConfigAbierto"
            :title="$configEmpresa ? 'Configuración · '.$configEmpresa->nombre : 'Configurar empresa'"
        >
            @if ($configEmpresa)
                <form wire:submit="guardarConfiguracion" class="space-y-4">
                    <x-ui.input
                        label="Nombre visible"
                        name="configNombre"
                        wire:model="configNombre"
                        required
                        :error="$errors->first('nombre')"
                    />

                    <x-ui.select
                        label="Zona horaria"
                        name="configZonaHoraria"
                        wire:model="configZonaHoraria"
                        :options="$zonaHorariaOptions"
                        :error="$errors->first('zona_horaria')"
                    />

                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.input
                            label="Huevos por maple"
                            name="configHuevosPorMaple"
                            type="number"
                            min="1"
                            wire:model="configHuevosPorMaple"
                            required
                            :error="$errors->first('huevos_por_maple')"
                        />

                        <x-ui.input
                            label="Maples por cajón"
                            name="configMaplesPorCajon"
                            type="number"
                            min="1"
                            wire:model="configMaplesPorCajon"
                            required
                            :error="$errors->first('maples_por_cajon')"
                        />
                    </div>

                    <div class="space-y-3 rounded-lg border border-avicore-border bg-avicore-surface px-4 py-3">
                        <p class="text-sm font-medium text-avicore-text">Logo de empresa</p>

                        @if ($configLogoUrl && ! $configQuitarLogo)
                            <img
                                src="{{ $configLogoUrl }}"
                                alt="Logo actual de {{ $configEmpresa->nombre }}"
                                class="h-16 w-auto max-w-full object-contain"
                            />
                        @endif

                        <input
                            type="file"
                            wire:model="configLogo"
                            accept="image/png,image/jpeg,image/webp,image/svg+xml"
                            class="block w-full text-sm text-avicore-muted file:mr-3 file:rounded-md file:border-0 file:bg-avicore-soft file:px-3 file:py-2 file:text-sm file:font-medium file:text-avicore-primary"
                        />
                        @error('logo')
                            <p class="text-sm text-avicore-danger" role="alert">{{ $message }}</p>
                        @enderror

                        @if ($configEmpresa->logo_path)
                            <label class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    wire:model="configQuitarLogo"
                                    class="size-4 rounded border-avicore-border-strong text-avicore-primary focus:ring-avicore-primary"
                                />
                                <span class="text-sm text-avicore-text">Quitar logo actual</span>
                            </label>
                        @endif
                    </div>

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <x-ui.button type="button" variant="secondary" wire:click="cerrarConfigurar">
                            Cancelar
                        </x-ui.button>
                        <x-ui.button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="guardarConfiguracion,configLogo"
                        >
                            <span wire:loading.remove wire:target="guardarConfiguracion,configLogo">Guardar</span>
                            <span wire:loading wire:target="guardarConfiguracion,configLogo">Guardando…</span>
                        </x-ui.button>
                    </div>
                </form>
            @endif
        </x-ui.dialog>

        <x-ui.dialog
            wire:model="dialogEstadoAbierto"
            :title="$editingEmpresa ? 'Estado · '.$editingEmpresa->nombre : 'Cambiar estado'"
        >
            @if ($editingEmpresa)
                <form wire:submit="guardarEstado" class="space-y-4">
                    <p class="text-sm text-avicore-muted">
                        Estado actual:
                        <strong class="text-avicore-text">{{ $editingEmpresa->estado->label() }}</strong>
                    </p>

                    @if ($ultimo = $editingEmpresa->ultimoCambioEstado())
                        <p class="text-xs text-avicore-muted">
                            Último cambio: {{ \Illuminate\Support\Carbon::parse($ultimo['fecha'])->format('d/m/Y H:i') }}
                            por {{ $ultimo['actor_name'] }}.
                        </p>
                    @endif

                    <x-ui.select
                        label="Nuevo estado"
                        name="estadoNuevo"
                        wire:model="estadoNuevo"
                        :options="$estadoOptions"
                        :error="$errors->first('estado')"
                    />

                    <x-ui.textarea
                        label="Motivo"
                        name="motivoEstado"
                        wire:model="motivoEstado"
                        placeholder="Ejemplo: falta de pago del servicio"
                        rows="3"
                        required
                        :error="$errors->first('motivo')"
                    />

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <x-ui.button type="button" variant="secondary" wire:click="cerrarCambioEstado">
                            Cancelar
                        </x-ui.button>
                        <x-ui.button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="guardarEstado"
                        >
                            <span wire:loading.remove wire:target="guardarEstado">Guardar estado</span>
                            <span wire:loading wire:target="guardarEstado">Guardando…</span>
                        </x-ui.button>
                    </div>
                </form>
            @endif
        </x-ui.dialog>

        <x-ui.dialog
            wire:model="dialogSoporteAbierto"
            :title="$soporteEmpresa ? 'Soporte · '.$soporteEmpresa->nombre : 'Ingresar en soporte'"
        >
            @if ($soporteEmpresa)
                <form wire:submit="ingresarSoporte" class="space-y-4">
                    <p class="text-sm leading-relaxed text-avicore-muted">
                        Vas a ver datos operativos de esta empresa en modo solo lectura.
                        El acceso queda auditado y caduca automáticamente.
                    </p>

                    <x-ui.textarea
                        label="Motivo del acceso"
                        name="motivoSoporte"
                        wire:model="motivoSoporte"
                        placeholder="Ejemplo: el cliente no puede ingresar y pidió revisar el resumen"
                        rows="3"
                        required
                        :error="$errors->first('motivo')"
                    />

                    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <x-ui.button type="button" variant="secondary" wire:click="cerrarSoporte">
                            Cancelar
                        </x-ui.button>
                        <x-ui.button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="ingresarSoporte"
                        >
                            <span wire:loading.remove wire:target="ingresarSoporte">Ingresar en soporte</span>
                            <span wire:loading wire:target="ingresarSoporte">Ingresando…</span>
                        </x-ui.button>
                    </div>
                </form>
            @endif
        </x-ui.dialog>

        <x-ui.dialog wire:model="dialogPasswordAbierto" title="Contraseña temporal del dueño">
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
