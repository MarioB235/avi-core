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
- Usuario prueba con `ultimo_galpon_id` = Galpón 1 (si entrás como operario).

**Demo completa (planificada):** ver [`plan-desarrollo.md`](../../avicore-contexto/references/plan-desarrollo.md) Bloque 7.

---

## 4. Usuario único de prueba

El seed crea **un solo usuario** (`AvicoreAuthSeeder`).

### Credenciales (copiar)

```text
Documento:  000000000
Contraseña: Avicore2026!
Nombre:     Usuario Prueba
```

| Campo | Valor |
|-------|--------|
| Nombre | Usuario Prueba |
| Documento | `000000000` |
| Contraseña | `Avicore2026!` |
| Rol inicial en BD | Dueño (cambia al entrar con el selector) |

Empresa: **Avícola Demo** (`DEMO`), excepto si elegís **Admin AviCore** en el selector (sin empresa).

### Cómo entrar

| Modo | Variable | Qué haces |
|------|----------|-----------|
| **Selector (MVP)** | `AVICORE_DEMO_LOGIN=true` | Elegís rol en **Perfil** → Ingresar. Sin documento ni contraseña. |
| **Login normal** | `AVICORE_DEMO_LOGIN=false` | Documento `000000000` + `Avicore2026!` (rol = el que quedó en BD tras el último login demo) |

Al elegir un rol en el selector, el sistema **actualiza ese mismo usuario** con el rol elegido y te loguea. Cerrás sesión, elegís otro rol → mismo usuario, distinto permiso.

**Primera vez / BD vacía:** `php artisan db:seed --force`. Re-ejecutar el seed es seguro (`firstOrCreate` en empresa y usuario demo).

**Antes de go-live real:** `AVICORE_DEMO_LOGIN=false` y redeploy.

### Rol → pantalla tras login

| Perfil en selector | Destino |
|--------------------|---------|
| Admin AviCore | `/admin` |
| Dueño, Administrativo, Encargado | `/admin` |
| Operario | `/operario` |
