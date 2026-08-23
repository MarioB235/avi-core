@php
    $profileRouteBase = $usesOperarioShell
        ? route('operario.perfil')
        : route('profile.edit');
@endphp

<div class="avicore-operario-perfil">
    <x-operario.perfil-hero :seccion="$seccion" />

    <div class="avicore-operario-home-sheet">
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
