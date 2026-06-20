<header class="avicore-admin-header avicore-admin-header--toolbar">
    <div @class([
        'avicore-admin-header__inner',
        'avicore-admin-header__inner--wide' => $contentWide ?? false,
    ])>
        @include('components.layouts.partials.admin-menu-trigger')

        <div class="avicore-admin-header__lead min-w-0 flex-1">
            <div class="avicore-admin-header__title-row">
                <h1 class="avicore-page-title truncate">{{ $heading ?? 'Panel' }}</h1>
                @isset($subheading)
                    <span class="avicore-admin-header__context truncate">{{ $subheading }}</span>
                @endisset
            </div>
        </div>

        <div class="avicore-admin-header__actions">
            @if ($showDate ?? false)
                <time datetime="{{ now()->toDateString() }}" class="avicore-date-pill avicore-date-pill--toolbar">
                    <x-ui.icon name="calendar" class="size-4 shrink-0" />
                    <span class="capitalize">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
                </time>
            @endif

            <button
                type="button"
                class="avicore-admin-header__icon-btn"
                disabled
                aria-label="Notificaciones (próximamente)"
            >
                <x-ui.icon name="bell" />
            </button>

            @auth
                <div class="avicore-admin-header__user">
                    <x-ui.user-avatar
                        :name="auth()->user()->name"
                        size="sm"
                        decorative
                        class="avicore-admin-header__user-avatar"
                    />
                    <div class="avicore-admin-header__user-text hidden min-w-0 sm:block">
                        <p class="truncate text-sm font-medium text-avicore-text">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-avicore-muted">{{ auth()->user()->rol->label() }}</p>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</header>
