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

### Login en local (selector)

Con `APP_ENV=local` y `AVICORE_DEMO_LOGIN=true` (ver `.env.example`), en `/login`:

- Documento y contraseña: vacíos y deshabilitados (no se usan).
- **Perfil:** `x-ui.select` de rol → autentica al usuario seedeado (`DemoLoginService` / `executeDemo`).

Desactivar: `AVICORE_DEMO_LOGIN=false` o entorno distinto de `local` (vuelve el login por documento + contraseña).

### Usuarios en base (mapeo por rol)

| Rol | Documento (seeder) | Después del login |
|---|---|---|
| Admin AviCore | `900000001` | `/admin` |
| Dueño | `100000001` | `/admin` |
| Administrativo | `300000001` | `/admin` |
| Encargado | `400000001` | `/admin` |
| Operario | `200000001` | `/operario` |

Contraseña seedeada `Avicore2026!` (solo aplica si el demo está desactivado y se usa login normal). Detalle de arranque: [`arranque-local.md`](../../avicore-contexto/references/arranque-local.md) § «Datos de prueba (login)».
