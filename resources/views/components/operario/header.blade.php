@props([
    'title' => 'Operario',
    'subtitle' => null,
    'hasGalpon' => false,
    'isGalponPage' => false,
    'isHomePage' => false,
])

<header @class([
    'avicore-operario-header',
    'avicore-operario-header--home' => $isHomePage,
])>
    <div class="avicore-operario-header__bar">
        @if ($isHomePage)
            <x-ui.logo size="sm" class="avicore-operario-header__logo" />

            <div class="avicore-operario-header__user">
                <div class="min-w-0 text-right">
                    <p class="avicore-operario-header__user-name">{{ auth()->user()->name }}</p>
                    <p class="avicore-operario-header__user-role">Operario</p>
                </div>
                <x-ui.user-avatar
                    :name="auth()->user()->name"
                    size="sm"
                    class="avicore-operario-header__avatar"
                />
            </div>
        @else
            <x-ui.logo size="sm" :showName="false" class="avicore-operario-header__logo" />

            <div class="avicore-operario-header__content">
                <p class="avicore-operario-header__badge">Operario</p>
                <h1 class="avicore-operario-header__title">{{ $title }}</h1>

                @if (! empty($subtitle))
                    @if ($isGalponPage)
                        <p class="avicore-operario-header__subtitle">{{ $subtitle }}</p>
                    @elseif ($hasGalpon)
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

            <x-ui.user-avatar
                :name="auth()->user()->name"
                size="sm"
                class="avicore-operario-header__avatar shrink-0"
            />
        @endif
    </div>
</header>
