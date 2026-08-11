@props([
    'avatarClass' => '',
    'size' => 'sm',
    'variant' => 'default',
])

@php
    use App\Services\AppBuildService;

    $user = auth()->user();
    $menuId = 'avicore-user-menu-'.uniqid();
    $isSidebar = $variant === 'sidebar';
    $buildLabel = app(AppBuildService::class)->labelForProfile();
    $onOperarioShell = request()->routeIs('operario.*');
    $profileRouteBase = ($onOperarioShell || $user->rol->isOperario())
        ? route('operario.perfil')
        : route('profile.edit');
@endphp

<div
    x-data="{
        open: false,
        view: 'menu',
        panelStyle: '',
        isSidebar: @js($isSidebar),
        syncPanelPosition() {
            const trigger = this.$refs.trigger;
            if (! trigger) {
                return;
            }

            const rect = trigger.getBoundingClientRect();
            const gap = 8;
            const edge = 8;
            const width = Math.min(272, window.innerWidth - (edge * 2));
            let left = rect.right - width;

            if (left < edge) {
                left = edge;
            }

            if (left + width > window.innerWidth - edge) {
                left = window.innerWidth - width - edge;
            }

            const panel = this.$refs.panel;
            const panelHeight = panel?.offsetHeight || 220;

            if (this.isSidebar) {
                let bottom = window.innerHeight - rect.top + gap;

                if (bottom + panelHeight > window.innerHeight - edge) {
                    bottom = Math.max(edge, window.innerHeight - panelHeight - edge);
                }

                this.panelStyle = `position:fixed;top:auto;right:auto;bottom:${bottom}px;left:${left}px;width:${width}px;max-height:calc(100vh - ${edge * 2}px);overflow-y:auto;z-index:80;`;

                return;
            }

            let top = rect.bottom + gap;

            if (top + panelHeight > window.innerHeight - edge) {
                top = Math.max(edge, rect.top - panelHeight - gap);
            }

            if (top < edge) {
                top = edge;
            }

            this.panelStyle = `position:fixed;top:${top}px;right:auto;bottom:auto;left:${left}px;width:${width}px;max-height:calc(100vh - ${edge * 2}px);overflow-y:auto;z-index:80;`;
        },
        openMenu() {
            this.open = true;
            this.view = 'menu';
            this.$nextTick(() => this.syncPanelPosition());
        },
        closeMenu() {
            this.open = false;
            this.view = 'menu';
        },
        toggleMenu() {
            if (this.open) {
                this.closeMenu();

                return;
            }

            this.openMenu();
        },
        onWindowClick(event) {
            if (! this.open) {
                return;
            }

            if (this.$refs.trigger?.contains(event.target)) {
                return;
            }

            if (this.$refs.panel?.contains(event.target)) {
                return;
            }

            this.closeMenu();
        },
    }"
    x-on:keydown.escape.window="if (open) closeMenu()"
    x-on:click.window="onWindowClick($event)"
    x-on:resize.window="if (open) syncPanelPosition()"
    x-on:scroll.window.capture="if (open) syncPanelPosition()"
    x-on:avicore-user-menu-action.window="closeMenu()"
    @class([
        'avicore-user-menu relative',
        'avicore-user-menu--sidebar' => $isSidebar,
    ])
>
    <button
        type="button"
        id="{{ $menuId }}-trigger"
        x-ref="trigger"
        @class([
            'avicore-user-menu__trigger',
            $avatarClass,
        ])
        aria-label="Abrir menú de cuenta"
        aria-haspopup="menu"
        x-bind:aria-expanded="open.toString()"
        aria-controls="{{ $menuId }}-panel"
        x-on:click.stop="toggleMenu()"
    >
        <x-ui.user-avatar
            :name="$user->name"
            :size="$size"
            decorative
        />
    </button>

    <template x-teleport="body">
        <div
            id="{{ $menuId }}-panel"
            x-ref="panel"
            x-show="open"
            x-cloak
            x-bind:style="panelStyle"
            x-transition:enter="transition ease-out duration-150 motion-reduce:transition-none"
            x-transition:enter-start="scale-95 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-100 motion-reduce:transition-none"
            x-transition:leave-start="scale-100 opacity-100"
            x-transition:leave-end="scale-95 opacity-0"
            @class([
                'avicore-user-menu__panel',
                'avicore-user-menu__panel--portal',
                'avicore-user-menu__panel--sidebar' => $isSidebar,
            ])
            role="menu"
            aria-labelledby="{{ $menuId }}-trigger"
            x-on:click.stop
        >
            <div x-show="view === 'menu'">
                <div class="avicore-user-menu__header">
                    <p class="avicore-user-menu__header-name">{{ $user->name }}</p>
                    <p class="avicore-user-menu__header-role">{{ $user->rol->label() }}</p>
                </div>

                <ul class="avicore-user-menu__list">
                    @if (config('avicore.pwa.enabled') && config('avicore.pwa.install_prompt'))
                        <li
                            role="none"
                            x-data="{
                                visible: false,
                                init() {
                                    if (! window.__avicorePwaInstall?.shouldShowMenuItem?.()) {
                                        return;
                                    }

                                    this.visible = true;

                                    window.addEventListener('avicore:pwa-install-ready', () => {
                                        this.visible = window.__avicorePwaInstall?.shouldShowMenuItem?.() ?? false;
                                    });

                                    window.addEventListener('avicore:pwa-installed', () => {
                                        this.visible = false;
                                    });
                                },
                                async installApp() {
                                    await window.__avicorePwaInstall?.offerInstall?.();
                                    this.$dispatch('avicore-user-menu-action');
                                },
                            }"
                            x-show="visible"
                            x-cloak
                        >
                            <button
                                type="button"
                                role="menuitem"
                                class="avicore-user-menu__item"
                                x-on:click="installApp()"
                            >
                                <x-ui.icon name="smartphone" class="avicore-user-menu__item-icon" />
                                Instalar app
                            </button>
                        </li>
                    @endif
                    <li role="none">
                        <button
                            type="button"
                            role="menuitem"
                            class="avicore-user-menu__item"
                            x-on:click="view = 'profile'"
                        >
                            <x-ui.icon name="users" class="avicore-user-menu__item-icon" />
                            Perfil
                        </button>
                    </li>
                    <li role="none">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                role="menuitem"
                                class="avicore-user-menu__item avicore-user-menu__item--danger"
                            >
                                <x-ui.icon name="logout" class="avicore-user-menu__item-icon" />
                                Cerrar sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            <div x-show="view === 'profile'" x-cloak>
                <div class="avicore-user-menu__profile-head">
                    <button
                        type="button"
                        class="avicore-user-menu__back"
                        x-on:click="view = 'menu'"
                    >
                        <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
                        Volver
                    </button>
                    <p class="avicore-user-menu__profile-title">Perfil</p>
                </div>

                <dl class="avicore-user-menu__profile-list">
                    <div>
                        <dt>Nombre</dt>
                        <dd>{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt>Documento</dt>
                        <dd>{{ $user->documento }}</dd>
                    </div>
                    @if ($user->email)
                        <div>
                            <dt>Correo</dt>
                            <dd>{{ $user->email }}</dd>
                        </div>
                    @endif
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

                <div class="avicore-user-menu__profile-actions">
                    <a
                        href="{{ $profileRouteBase }}"
                        wire:navigate
                        role="menuitem"
                        class="avicore-user-menu__item"
                        x-on:click="closeMenu()"
                    >
                        <x-ui.icon name="users" class="avicore-user-menu__item-icon" />
                        Editar datos
                    </a>
                    <a
                        href="{{ $profileRouteBase }}?seccion=password"
                        wire:navigate
                        role="menuitem"
                        class="avicore-user-menu__item"
                        x-on:click="closeMenu()"
                    >
                        <x-ui.icon name="lock-keyhole" class="avicore-user-menu__item-icon" />
                        Cambiar contraseña
                    </a>
                </div>
            </div>
        </div>
    </template>
</div>
