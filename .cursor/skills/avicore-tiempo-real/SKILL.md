---
name: avicore-tiempo-real
description: Implementa eventos Laravel Reverb y Echo en AviCore para dashboard, alertas y supervisión. Usar cuando se necesita actualización en tiempo real, WebSockets o canales privados por empresa.
---

# AviCore — Tiempo real (Reverb + Echo)

## Leer primero

| Referencia | Contenido |
|------------|-----------|
| [`references/eventos.md`](references/eventos.md) | Eventos y canales |
| `avicore-contexto/references/arquitectura.md` | Stack |
| `avicore-negocio/references/` | Reglas y permisos |

## Reglas

- Todo evento respeta `empresa_id`; nunca cruzar empresas
- No usar tiempo real en CRUD simple sin valor
- Auditar si surge de acción crítica

## Verificación

1. Cargar dato en una sesión
2. Ver actualización en otra sesión
3. Usuario de otra empresa sin acceso al canal
