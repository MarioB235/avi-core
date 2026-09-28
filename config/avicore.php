<?php

use App\Enums\UserRole;

return [

    /*
    |--------------------------------------------------------------------------
    | Versión de producto (semver)
    |--------------------------------------------------------------------------
    |
    | Número visible en Perfil y soporte. El build desplegado (fecha + commit)
    | se genera aparte en public/build/avicore-build.json al hacer pnpm run build.
    |
    */

    'version' => env('AVICORE_VERSION', '0.1.0-dev'),

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
    | Modo soporte auditado (Admin AviCore — EMP-06)
    |--------------------------------------------------------------------------
    |
    | Sesión con motivo, actor, inicio/fin y caducidad. Sin soporte activo el
    | admin no ve datos operativos de clientes.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Datos personales (EMP-08)
    |--------------------------------------------------------------------------
    |
    | Política operativa MVP documentada en avicore-negocio/references/datos-personales.md.
    | No constituye asesoramiento legal ni certificación de cumplimiento normativo.
    |
    */

    'datos_personales' => [
        'documento_visible_digitos' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retención D07 (AUD-08)
    |--------------------------------------------------------------------------
    |
    | Plazos operativos acordados para MVP. No constituyen obligación legal MGAP/SMA
    | sin validación D05. Purga deshabilitada por defecto; ver retencion-d07.md.
    |
    */

    'retencion' => [
        'd07' => [
            'nota' => 'Plazos operativos acordados MVP; no constituyen certificación legal sin validación D05.',
            'operativa_meses' => (int) env('AVICORE_RETENCION_OPERATIVA_MESES', 60),
            'auditoria_meses' => (int) env('AVICORE_RETENCION_AUDITORIA_MESES', 60),
            'correcciones_meses' => (int) env('AVICORE_RETENCION_CORRECCIONES_MESES', 60),
            'documentos_emitidos_meses' => (int) env('AVICORE_RETENCION_DOCUMENTOS_MESES', 60),
            'usuarios_meses' => (int) env('AVICORE_RETENCION_USUARIOS_MESES', 60),
            'documentos_disk' => env('AVICORE_RETENCION_DOCUMENTOS_DISK', 'local'),
            'purge_habilitado' => (bool) env('AVICORE_RETENCION_PURGE', false),
        ],
    ],

    'soporte' => [
        'duracion_minutos' => (int) env('AVICORE_SOPORTE_DURACION_MINUTOS', 120),
        'motivo_min_caracteres' => (int) env('AVICORE_SOPORTE_MOTIVO_MIN', 10),
        'destino_entrada' => 'avicore.resumen.index',
        'destino_salida_default' => 'avicore.empresas.index',
        'destinos_salida' => [
            'avicore.empresas.index',
            'avicore.home',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Login demo (selector de perfil, sin credenciales)
    |--------------------------------------------------------------------------
    |
    | AVICORE_DEMO_LOGIN=true: selector Perfil en /login (sin credenciales).
    | Cada perfil usa un usuario demo fijo (role_documentos); no se muta el rol en BD.
    | El selector aparece con el flag (no en production); el ingreso requiere empresa demo y usuarios del seed.
    | En APP_ENV=production el selector queda deshabilitado siempre (DemoLoginService).
    | Desactivar (false) antes de go-live con clientes reales.
    |
    */

    'demo_login' => [
        'enabled_flag' => env('AVICORE_DEMO_LOGIN', false),
        'empresa_codigo' => env('AVICORE_DEMO_EMPRESA_CODIGO', 'DEMO'),
        'role_documentos' => [
            UserRole::Dueno->value => env('AVICORE_DEMO_DOCUMENTO_DUENO', '000000000'),
            UserRole::Administrativo->value => env('AVICORE_DEMO_DOCUMENTO_ADMINISTRATIVO', '66666666'),
            UserRole::Encargado->value => env('AVICORE_DEMO_DOCUMENTO_ENCARGADO', '55555555'),
            UserRole::Operario->value => env('AVICORE_DEMO_DOCUMENTO_OPERARIO', '11111111'),
            UserRole::Reparto->value => env('AVICORE_DEMO_DOCUMENTO_REPARTO', '44444444'),
            UserRole::AdminAvicore->value => env('AVICORE_DEMO_DOCUMENTO_ADMIN_AVICORE', '900000000'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PWA (instalable en móvil)
    |--------------------------------------------------------------------------
    |
    | enabled: manifest + service worker (assets estáticos; sin offline completo).
    | install_prompt: banner «Instalar» cuando el navegador lo permite (o guía iOS).
    |
    */

    'pwa' => [
        'enabled' => env('AVICORE_PWA_ENABLED', true),
        'install_prompt' => env('AVICORE_PWA_INSTALL_PROMPT', true),
    ],

];
