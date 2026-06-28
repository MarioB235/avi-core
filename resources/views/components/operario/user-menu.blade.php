@props([
    'avatarClass' => '',
    'size' => 'sm',
])

@php
    $user = auth()->user();
    $menuId = 'operario-user-menu-'.uniqid();
@endphp

<div
    x-data="{ open: false, view: 'menu' }"
    x-on:keydown.escape.window="if (open) { open = false; view = 'menu'; }"
    x-on:click.outside="open = false; view = 'menu'"
    class="avicore-operario-user-menu relative"
>
    <button
        type="button"
        id="{{ $menuId }}-trigger"
        @class([
            'avicore-operario-user-menu__trigger',
            $avatarClass,
        ])
        aria-label="Abrir menú de cuenta"
        aria-haspopup="menu"
        x-bind:aria-expanded="open.toString()"
        aria-controls="{{ $menuId }}-panel"
        x-on:click.stop="open = ! open; if (open) { view = 'menu'; }"
    >
        <x-ui.user-avatar
            :name="$user->name"
            :size="$size"
            decorative
        />
    </button>

    <div
        id="{{ $menuId }}-panel"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150 motion-reduce:transition-none"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-100 motion-reduce:transition-none"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="avicore-operario-user-menu__panel"
        role="menu"
        aria-labelledby="{{ $menuId }}-trigger"
    >
        <div x-show="view === 'menu'">
            <div class="avicore-operario-user-menu__header">
                <p class="avicore-operario-user-menu__header-name">{{ $user->name }}</p>
                <p class="avicore-operario-user-menu__header-role">{{ $user->rol->label() }}</p>
            </div>

            <ul class="avicore-operario-user-menu__list">
                <li role="none">
                    <button
                        type="button"
                        role="menuitem"
                        class="avicore-operario-user-menu__item"
                        x-on:click="view = 'profile'"
                    >
                        <x-ui.icon name="users" class="avicore-operario-user-menu__item-icon" />
                        Perfil
                    </button>
                </li>
                <li role="none">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            role="menuitem"
                            class="avicore-operario-user-menu__item avicore-operario-user-menu__item--danger"
                        >
                            <x-ui.icon name="logout" class="avicore-operario-user-menu__item-icon" />
                            Cerrar sesión
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <div x-show="view === 'profile'" x-cloak>
            <div class="avicore-operario-user-menu__profile-head">
                <button
                    type="button"
                    class="avicore-operario-user-menu__back"
                    x-on:click="view = 'menu'"
                >
                    <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
                    Volver
                </button>
                <p class="avicore-operario-user-menu__profile-title">Perfil</p>
            </div>

            <dl class="avicore-operario-user-menu__profile-list">
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
            </dl>
        </div>
    </div>
</div>
