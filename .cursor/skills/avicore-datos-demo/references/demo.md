# 10 — Datos demo

## 1. Objetivo

Definir datos ficticios para demos y pruebas.

---

## 2. Empresa demo

Nombre:

```text
Avícola Demo
```

---

## 3. Estructura

**Seed mínimo (implementado, `AvicoreEstructuraAvicolaSeeder`):**

- 1 empresa demo (`Avícola Demo`).
- 1 granja (Granja Norte).
- 2 galpones (G-01, G-02).
- 1 lote activo en Galpón 1.
- Operario demo con `ultimo_galpon_id` = Galpón 1.

**Demo completa (planificada):** múltiples granjas/galpones, 30 días de registros, escenarios para gráficos y reportes — ver [`plan-desarrollo.md`](../../avicore-contexto/references/plan-desarrollo.md) Bloque 7 y skill `avicore-datos-demo`. No documentar aquí el detalle hasta que exista el seeder o dataset.

---

## 4. Usuarios demo (auth — Bloque 2)

Cargados con `AvicoreAuthSeeder` (`php artisan db:seed`). Empresa: **Avícola Demo** (`DEMO`).

### Login rápido en local

Con `APP_ENV=local` y `AVICORE_DEMO_LOGIN=true` (ver `.env.example`), en `/login`:

- Documento: `000000000`
- Contraseña: `Avicore2026!`
- **Perfil demo:** `x-ui.select` de rol (resuelve al usuario seedeado correspondiente vía `DemoLoginService`).

Desactivar para entornos reales: `AVICORE_DEMO_LOGIN=false` o despliegue fuera de `local`.

### Usuarios en base (mapeo por rol)

| Rol | Documento | Contraseña | Cambio obligatorio |
|---|---|---|---|
| Admin AviCore | `900000001` | `Avicore2026!` | No |
| Dueño | `100000001` | `Avicore2026!` | No |
| Administrativo | `300000001` | `Avicore2026!` | No |
| Encargado | `400000001` | `Avicore2026!` | No |
| Operario | `200000001` | `Avicore2026!` | No |

El login por documento individual sigue disponible (útil para pruebas puntuales o flujo de cambio de contraseña con usuarios de factory en tests).

Credenciales para probar login en local: [`avicore-contexto/references/arranque-local.md`](../../avicore-contexto/references/arranque-local.md) § «Datos de prueba (login)».
