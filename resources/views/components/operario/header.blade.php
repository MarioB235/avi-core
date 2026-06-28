@props([
    'title' => 'Operario',
    'subtitle' => null,
    'hasGalpon' => false,
    'isHomePage' => false,
])

<header @class([
    'avicore-operario-header',
    'avicore-operario-header--home' => $isHomePage,
])>
    <div class="avicore-operario-header__bar">
        @if ($isHomePage)
            <div class="avicore-home-nav">
                <svg
                    class="avicore-home-nav__curve"
                    viewBox="0 0 390 92"
                    preserveAspectRatio="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                >
                    <defs>
                        <linearGradient id="avicoreNavLine" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="var(--color-avicore-secondary)" stop-opacity="1" />
                            <stop offset="38%" stop-color="var(--color-avicore-secondary)" stop-opacity="0.98" />
                            <stop offset="70%" stop-color="var(--color-avicore-primary)" stop-opacity="0.35" />
                            <stop offset="100%" stop-color="var(--color-avicore-soft)" stop-opacity="0.5" />
                        </linearGradient>
                    </defs>

                    <path
                        class="avicore-home-nav__shape"
                        d="M0 0 H390 V10 H300 C260 10 238 15 216 36 C194 58 178 66 150 66 H0 Z"
                    />

                    <path
                        class="avicore-home-nav__line-main"
                        d="M0 66 H150 C178 66 194 58 216 36 C238 15 260 10 300 10 H390"
                    />
                </svg>

                <div class="avicore-home-nav__grid">
                    <div class="avicore-home-nav__brand">
                        <x-ui.logo size="sm" class="avicore-home-nav__logo" />
                    </div>

                    <div class="avicore-home-nav__middle" aria-hidden="true"></div>

                    <div class="avicore-home-nav__account">
                        <div class="avicore-home-nav__account-copy">
                            <p class="avicore-home-nav__account-name">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="avicore-home-nav__account-role">
                                {{ auth()->user()->rol->label() }}
                            </p>
                        </div>

                        <x-operario.user-menu
                            size="sm"
                            avatar-class="avicore-home-nav__avatar shrink-0"
                        />
                    </div>
                </div>
            </div>
        @else
            <x-ui.logo size="sm" :showName="false" class="avicore-operario-header__logo" />

            <div class="avicore-operario-header__content">
                <p class="avicore-operario-header__badge">Operario</p>
                <h1 class="avicore-operario-header__title">{{ $title }}</h1>

                @if (! empty($subtitle))
                    @if ($hasGalpon)
                        <div class="avicore-operario-header__chip avicore-operario-header__chip--active">
                            <span class="truncate">{{ $subtitle }}</span>
                        </div>
                    @else
                        <div class="avicore-operario-header__chip avicore-operario-header__chip--empty">
                            <x-ui.icon name="warehouse" class="size-3.5 shrink-0" />
                            <span class="truncate">{{ $subtitle }}</span>
                        </div>
                    @endif
                @endif
            </div>

            <x-operario.user-menu
                size="sm"
                avatar-class="avicore-operario-header__avatar shrink-0"
            />
        @endif
    </div>
</header>
