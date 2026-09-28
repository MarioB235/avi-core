# CAP-12 — Perfil y ayuda

**ID:** CAP-12  
**Estado:** VERIFICADA  
**Fecha:** 2026-09-27  
**Rama:** `feature/cap-est-operacion-estructura`

## Objetivo

Perfil de autogestión con datos permitidos (nombre, correo), cambio de contraseña y contacto de ayuda real; **sin** editar rol, empresa ni documento.

## Implementación

| Área | Cambio |
|------|--------|
| Datos | `UpdateProfileAction` persiste solo `name`/`email` validados |
| UI | Pestaña **Ayuda** + `x-support.contact-links` desde `SupportContactService` |
| Solo lectura | Documento, rol, empresa en `datos-form`; copy en ayuda |
| Rutas | `/operario/perfil` y `/perfil` comparten `Profile/Edit` |

## Prueba de cierre

```bash
php artisan test tests/Feature/Operario/OperarioPerfilCap12Test.php
php artisan test tests/Feature/Operario/OperarioPerfilTest.php
php artisan test
vendor/bin/pint --dirty
pnpm run check:agent-docs
```

## Resultado (2026-09-27)

- `OperarioPerfilCap12Test`: 3/3 OK
- `OperarioPerfilTest`: 10/10 OK (regresión)
- Suite completa: **788/788** OK · 2667 aserciones · Pint OK · `check:agent-docs` OK

## Contrato

- `reglas.md` §8.10
- `pantallas-flujos.md` — nota CAP-12
