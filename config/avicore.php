<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Soporte para recuperación de acceso (MVP)
    |--------------------------------------------------------------------------
    |
    | En MVP no hay reset automático por correo. El usuario contacta soporte
    | o a su administrador de empresa; estos datos se muestran en login.
    |
    | Validación en runtime: App\Services\SupportContactService (WhatsApp requiere
    | dígitos; correo debe ser FILTER_VALIDATE_EMAIL). Si ambos fallan, el diálogo
    | muestra mensaje genérico sin enlaces rotos.
    |
    */

    'support' => [
        'whatsapp' => env('AVICORE_SUPPORT_WHATSAPP', '+5491123456789'),
        'whatsapp_display' => env('AVICORE_SUPPORT_WHATSAPP_DISPLAY', '+54 9 11 2345-6789'),
        'email' => env('AVICORE_SUPPORT_EMAIL', 'soporte@avicore.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Login demo (selector de perfil, sin credenciales)
    |--------------------------------------------------------------------------
    |
    | AVICORE_DEMO_LOGIN=true: selector Perfil en /login (sin credenciales).
    | Un solo usuario demo (AVICORE_DEMO_DOCUMENTO); el rol elegido se aplica al entrar.
    | Desactivar (false) antes de go-live con clientes reales.
    |
    */

    'demo_login' => [
        'enabled_flag' => env('AVICORE_DEMO_LOGIN', false),
        'documento' => env('AVICORE_DEMO_DOCUMENTO', '000000000'),
    ],

];
