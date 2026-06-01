<div class="flex h-full flex-col">
    <div class="border-b border-avicore-border px-5 py-5">
        <x-ui.logo />
    </div>

    @include('components.layouts.partials.admin-nav')

    <div class="mt-auto border-t border-avicore-border px-3 py-4">
        @auth
            <p class="mb-3 truncate px-2 text-xs text-avicore-muted">{{ auth()->user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="avicore-nav-link w-full text-left">
                    <x-ui.icon name="logout" class="size-5 shrink-0" />
                    Cerrar sesión
                </button>
            </form>
        @endauth
    </div>
</div>
