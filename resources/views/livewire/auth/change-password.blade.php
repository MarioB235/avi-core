<div>
    <div class="avicore-auth-card">
        <header class="avicore-auth-card__header">
            <h2 class="avicore-auth-card__title">Nueva contraseña</h2>
            <p class="avicore-auth-card__subtitle">Actualizá tu acceso para continuar</p>
        </header>

        <x-ui.alert variant="warning" class="mb-5">
            Por seguridad debés definir una contraseña nueva antes de continuar.
        </x-ui.alert>

        <form wire:submit="save" class="space-y-5">
            <x-ui.input
                label="Contraseña actual"
                name="current_password"
                icon="lock-keyhole"
                toggle-password
                wire:model="current_password"
                autocomplete="current-password"
                placeholder="Ingresá tu contraseña temporal"
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
                <span wire:loading.remove wire:target="save">Guardar contraseña</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </x-ui.button>
        </form>

        <x-auth.support-contact-dialog
            trigger="¿Problemas con tu contraseña temporal?"
            intro="Si no recordás la contraseña temporal o no podés ingresar, contactá a tu administrador o a soporte indicando tu documento registrado."
        />
    </div>
</div>
