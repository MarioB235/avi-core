@php
    $profileRouteBase = $usesOperarioShell
        ? route('operario.perfil')
        : route('profile.edit');
@endphp

<div @class(['avicore-operario-perfil' => $usesOperarioShell])>
    @if ($usesOperarioShell)
        <x-operario.perfil-hero :seccion="$seccion" />
    @endif

    <div @class([
        'avicore-operario-home-sheet' => $usesOperarioShell,
        'mx-auto max-w-2xl px-4 py-6 lg:px-0' => ! $usesOperarioShell,
    ])>
        @unless ($usesOperarioShell)
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
        @endunless

        @include('livewire.profile.partials.tabs', [
            'seccion' => $seccion,
            'profileRouteBase' => $profileRouteBase,
        ])

        @if ($seccion === 'password')
            @include('livewire.profile.partials.password-form')
        @else
            @include('livewire.profile.partials.datos-form', [
                'user' => $user,
                'buildLabel' => $buildLabel,
            ])
        @endif
    </div>
</div>
