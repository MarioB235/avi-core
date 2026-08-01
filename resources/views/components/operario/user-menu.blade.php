@props([
    'avatarClass' => '',
    'size' => 'sm',
    'variant' => 'default',
])

{{-- Alias: el menú cuenta compartido vive en x-ui.user-menu --}}
<x-ui.user-menu
    :avatar-class="$avatarClass"
    :size="$size"
    :variant="$variant"
    {{ $attributes }}
/>
