---
name: avicore-deuda-tecnica
description: Recolecta comentarios avicore-defer en el repo en un ledger de deuda técnica consciente. Solo lectura salvo que el usuario pida persistir. Interno; mensaje 1 (pedir ledger) o pasada msg 4.
disable-model-invocation: true
---

# AviCore — Deuda técnica (`avicore-defer`)

Convención en [`estandares-codigo.md`](../avicore-auditoria/references/estandares-codigo.md) § Simplificación. Ledger vía comentarios `avicore-defer:`; **no** usar marcadores de otros proyectos en código de producto AviCore.

## Convención

Comentario: `avicore-defer: <techo>, <disparador para revisar>`

Ejemplos:

```php
// avicore-defer: sin cache de permisos, revisar si >50 roles por empresa
```

```blade
{{-- avicore-defer: KPI hardcodeado, conectar a query cuando exista módulo producción --}}
```

## Escaneo

Buscar en el repo (excluir `vendor/`, `node_modules/`, `public/build/`, `.git`):

Patrones: `# avicore-defer:`, `// avicore-defer:`, `{{-- avicore-defer:`, `/* avicore-defer:`

## Salida

Una fila por marcador, agrupado por archivo:

`<archivo>:<línea> — <qué se simplificó>. techo: <límite>. disparador: <cuándo revisar>.`

Marcadores sin disparador claro: etiqueta `sin-disparador` (riesgo de olvido).

Cierre: `<N> marcadores, <M> sin disparador.` Si no hay ninguno: `Sin avicore-defer en el repo.`

## Boundaries

- **Solo lectura** por defecto; no modificar código.
- Persistir ledger solo si el usuario lo pide (skill `avicore-evolucion-tooling`).
- No confundir con auditoría msg 2 (multidimensional); este skill solo lista deferencias marcadas.
