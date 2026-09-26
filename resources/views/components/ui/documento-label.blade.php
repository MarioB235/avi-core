@props([
    'user',
    'viewer' => null,
])

@php
    $viewer = $viewer ?? auth()->user();
    $label = $user instanceof \App\Models\User && $viewer instanceof \App\Models\User
        ? \App\Support\DatosPersonales::documentoParaVista($viewer, $user)
        : \App\Support\DatosPersonales::maskDocumento((string) ($user->documento ?? ''));
    $full = $user instanceof \App\Models\User && $viewer instanceof \App\Models\User
        && \App\Support\DatosPersonales::canViewFullDocumento($viewer, $user);
@endphp

<span
    {{ $attributes->merge(['class' => '']) }}
    @if (! $full) title="Documento parcial por política de privacidad operativa" @endif
>{{ $label }}</span>
