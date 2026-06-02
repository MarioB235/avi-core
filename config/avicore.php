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

];
