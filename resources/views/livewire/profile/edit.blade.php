<div @class(['avicore-operario-perfil' => $usesOperarioShell])>
    @if ($usesOperarioShell)
        <div wire:key="perfil-hero-{{ $seccion }}">
            <x-operario.perfil-hero :seccion="$seccion" />
        </div>
    @endif

    <div @class([
        'avicore-operario-home-sheet' => $usesOperarioShell,
        'mx-auto max-w-2xl px-4 py-6 lg:px-0' => ! $usesOperarioShell,
    ])>
        @if (! $usesOperarioShell)
            <header class="mb-6">
                <h1 class="text-2xl font-bold text-avicore-text">
                    {{ $seccion === 'password' ? 'Contraseña' : 'Mis datos' }}
                </h1>
                <p class="mt-1 text-sm text-avicore-muted">
                    {{ $seccion === 'password'
                        ? 'Cambiá tu clave de acceso.'
                        : 'Actualizá tu nombre y correo de contacto.' }}
                </p>
            </header>
        @endif

        <nav class="avicore-operario-perfil__tabs" aria-label="Secciones del perfil" role="tablist">
            <button
                type="button"
                wire:click="seleccionarSeccion('datos')"
                role="tab"
                aria-selected="{{ $seccion === 'datos' ? 'true' : 'false' }}"
                @class([
                    'avicore-operario-perfil__tab',
                    'avicore-operario-perfil__tab--active' => $seccion === 'datos',
                ])
            >
                Mis datos
            </button>
            <button
                type="button"
                wire:click="seleccionarSeccion('password')"
                role="tab"
                aria-selected="{{ $seccion === 'password' ? 'true' : 'false' }}"
                @class([
                    'avicore-operario-perfil__tab',
                    'avicore-operario-perfil__tab--active' => $seccion === 'password',
                ])
            >
                Contraseña
            </button>
        </nav>

        @if ($seccion === 'password')
            <section class="avicore-operario-perfil__section" aria-label="Cambiar contraseña">
                <form wire:submit="guardarContrasena" class="space-y-4">
                    <x-ui.input
                        label="Contraseña actual"
                        name="current_password"
                        icon="lock-keyhole"
                        toggle-password
                        wire:model="current_password"
                        autocomplete="current-password"
                        placeholder="Tu contraseña actual"
                        required
                    />

                    <x-ui.input
                        label="Nueva contraseña"
                        name="password"
                        icon="key-round"
                        toggle-password
                        wire:model="password"
                        autocomplete="new-password"
                        placeholder="Mínimo 8 caracteres"
                        hint="8+ caracteres, mayúsculas, minúsculas y números."
                        required
                    />

                    <x-ui.input
                        label="Confirmar nueva contraseña"
                        name="password_confirmation"
                        icon="shield-check"
                        toggle-password
                        wire:model="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Repetí la nueva contraseña"
                        required
                    />

                    <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="guardarContrasena">Guardar contraseña</span>
                        <span wire:loading wire:target="guardarContrasena">Guardando…</span>
                    </x-ui.button>
                </form>

                <x-auth.support-contact-dialog
                    trigger="¿Olvidaste tu contraseña?"
                    intro="Si no recordás tu contraseña, contactá a tu administrador o a soporte indicando tu documento registrado."
                    class="mt-4"
                />
            </section>
        @else
            <section class="avicore-operario-perfil__section" aria-label="Mis datos">
                <form wire:submit="guardarDatos" class="space-y-4">
                    <x-ui.input
                        label="Nombre"
                        name="name"
                        icon="users"
                        wire:model="name"
                        autocomplete="name"
                        placeholder="Tu nombre"
                        required
                    />

                    <x-ui.input
                        label="Correo"
                        name="email"
                        type="email"
                        wire:model="email"
                        autocomplete="email"
                        placeholder="correo@ejemplo.com"
                    />

                    <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="guardarDatos">Guardar datos</span>
                        <span wire:loading wire:target="guardarDatos">Guardando…</span>
                    </x-ui.button>
                </form>

                <dl class="avicore-operario-perfil__readonly">
                    <div>
                        <dt>Documento</dt>
                        <dd>{{ $user->documento }}</dd>
                    </div>
                    @if ($user->empresa)
                        <div>
                            <dt>Empresa</dt>
                            <dd>{{ $user->empresa->nombre }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt>Rol</dt>
                        <dd>{{ $user->rol->label() }}</dd>
                    </div>
                    @if ($buildLabel)
                        <div>
                            <dt>Versión</dt>
                            <dd>{{ $buildLabel }}</dd>
                        </div>
                    @endif
                </dl>
            </section>
        @endif
    </div>
</div>
