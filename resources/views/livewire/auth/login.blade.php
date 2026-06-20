<div>
    <div class="avicore-auth-card">
        <header class="avicore-auth-card__header">
            <h2 class="avicore-auth-card__title">Bienvenido</h2>
            <p class="avicore-auth-card__subtitle">Iniciá sesión para continuar</p>
        </header>

        <form wire:submit="login" class="space-y-5">
            <x-ui.input
                label="Documento"
                name="documento"
                icon="id-card"
                wire:model="documento"
                autocomplete="username"
                placeholder="Ingresá tu número de documento"
                required
            />

            <x-ui.input
                label="Contraseña"
                name="password"
                icon="lock-keyhole"
                toggle-password
                wire:model="password"
                autocomplete="current-password"
                placeholder="Ingresá tu contraseña"
                required
            />

            @if ($demoLoginEnabled)
                <div class="space-y-1.5">
                    <label for="demoRole" class="block text-sm font-medium text-avicore-text">
                        Perfil demo
                    </label>
                    <select
                        id="demoRole"
                        name="demoRole"
                        wire:model="demoRole"
                        class="avicore-input avicore-input--plain block w-full min-h-11 rounded-lg border border-avicore-border-strong bg-avicore-card px-3 py-2.5 text-sm text-avicore-text outline-none transition-colors focus:border-avicore-primary"
                    >
                        @foreach ($demoRoles as $role)
                            <option value="{{ $role->value }}">{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-avicore-muted">Modo demo — solo desarrollo local</p>
                    @error('demoRole')
                        <p class="text-sm text-avicore-danger" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <label class="flex min-h-11 cursor-pointer items-center gap-2 text-sm text-avicore-muted">
                <input
                    type="checkbox"
                    wire:model="remember"
                    class="avicore-checkbox"
                />
                Recordarme
            </label>

            <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Iniciar sesión</span>
                <span wire:loading wire:target="login">Ingresando…</span>
            </x-ui.button>
        </form>

        <x-auth.support-contact-dialog />
    </div>
</div>
