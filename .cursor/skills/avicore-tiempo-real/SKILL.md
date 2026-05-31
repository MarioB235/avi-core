---
name: avicore-tiempo-real
description: Implementa eventos Laravel Reverb y Echo en AviCore para dashboard, alertas y supervisión. Usar cuando se necesita actualización en tiempo real, WebSockets o canales privados por empresa.
disable-model-invocation: true
---

# AviCore — Tiempo real (Reverb + Echo)

## Documentación

- `docs/08-tiempo-real-eventos.md`
- `docs/07-arquitectura-tecnica.md`
- `docs/05-reglas-de-negocio.md`
- `docs/06-roles-y-permisos.md`

## Definir

- Evento Laravel, canal privado, payload mínimo
- Componente que escucha y roles autorizados
- Si afecta dashboard, alertas o supervisión

## Reglas

- Todo evento respeta `empresa_id`; nunca cruzar empresas
- No usar tiempo real en CRUD simple sin valor
- Auditar si surge de acción crítica

## No usar tiempo real en

CRUD empresas/granjas/galpones/lotes/usuarios, configuración, exportaciones.

## Verificación

1. Cargar dato en una sesión
2. Ver actualización en otra sesión
3. Usuario de otra empresa sin acceso al canal

## Entrada del usuario

Evento o flujo a implementar.
