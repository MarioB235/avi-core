<div class="space-y-8">
    <x-ui.logo subtitle="Iniciar sesión" class="justify-center" />

    <x-ui.card>
        <form wire:submit="login" class="space-y-5">
            <x-ui.input
                label="Documento"
                name="documento"
                wire:model="documento"
                autocomplete="username"
                required
            />

            <x-ui.input
                label="Contraseña"
                name="password"
                type="password"
                wire:model="password"
                autocomplete="current-password"
                required
            />

            <label class="flex min-h-11 cursor-pointer items-center gap-3 text-sm text-avicore-muted">
                <input
                    type="checkbox"
                    wire:model="remember"
                    class="size-4 rounded border-avicore-border-strong text-avicore-primary focus:ring-2 focus:ring-avicore-primary/30"
                />
                Recordarme en este equipo
            </label>

            <x-ui.button type="submit" class="w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Iniciar sesión</span>
                <span wire:loading wire:target="login">Ingresando…</span>
            </x-ui.button>
        </form>
    </x-ui.card>
</div>
