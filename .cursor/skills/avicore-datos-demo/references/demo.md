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
- 1 lote activo en Galpón 1 (con `codigo_sma` demo `L-2024-089`).
- Usuario prueba con `ultimo_galpon_id` = Galpón 1 (si entrás como operario).
- **Cargas demo** (`AvicoreOperarioDemoSeeder`): huevos 1200 + 30 descarte, 2 muertes, 1 descarte de aves, alimento 8500 kg (hace 2 días), huevos de ayer, vacunación Gumboro (hace 3 días) — solo si el galpón no tenía registros.
- **Panel Dueño demo** (`AvicoreDuenoDemoSeeder`): lote en Galpón 2, historial de postura 7 días (ambos galpones) y carga de huevos de hoy en G-02 — idempotente por fecha/galpón.
- **Equipo demo** (`AvicoreEquipoDemoSeeder`): 6 personas ficticias en Avícola Demo (3 operarios, 1 reparto, 1 encargado, 1 administrativo) para poblar el directorio en `/dueno/equipo`. El Dueño sigue siendo `Usuario Prueba` (`000000000`). Idempotente (`firstOrCreate` por documento).

**Demo completa (planificada):** ver [`plan-desarrollo.md`](../../avicore-contexto/references/plan-desarrollo.md) Bloque 7.

---

## 4. Usuarios de prueba (login demo)

El seed crea usuarios fijos por rol (`AvicoreAuthSeeder` + `AvicoreEquipoDemoSeeder`). El selector **no muta** el rol en BD: cada perfil entra con su usuario demo.

### Credenciales (copiar)

Contraseña común en seed: `Avicore2026!`

| Perfil | Documento | Nombre |
|--------|-----------|--------|
| Dueño | `000000000` | Usuario Prueba |
| Administrativo | `66666666` | Laura Fernández |
| Encargado | `55555555` | Roberto Méndez |
| Operario | `11111111` | María López |
| Reparto | `44444444` | Diego Souza |
| Admin AviCore | `900000000` | Admin Demo AviCore |

Empresa: **Avícola Demo** (`DEMO`), excepto **Admin AviCore** (sin `empresa_id`).

### Cómo entrar

| Modo | Variable | Qué haces |
|------|----------|-----------|
| **Selector (MVP)** | `AVICORE_DEMO_LOGIN=true` | Elegís rol en **Perfil** → Ingresar. Sin documento ni contraseña. |
| **Login normal** | `AVICORE_DEMO_LOGIN=false` | Documento + contraseña del perfil que quieras probar. |

### Guards de seguridad (`DemoLoginService`)

1. **`APP_ENV=production`:** selector deshabilitado siempre (aunque el flag diga `true`). En producción usá documento + contraseña (`000000000` / `Avicore2026!` tras seed).
2. **Sin empresa demo:** con el flag activo el selector **sí** se muestra; `/login` avisa que faltan datos demo y el envío falla en `demoRole` hasta cargar el seed (`migrate --seed`). No hay documento/contraseña en pantalla en este modo.
3. **Sin usuarios demo (o seed parcial):** igual que (2): selector visible, aviso en pantalla y mensaje en `demoRole` al intentar ingresar hasta que todos los `role_documentos` estén activos en BD.
4. **Solo usuarios demo:** el login por selector rechaza usuarios fuera de Avícola Demo (o Admin AviCore demo sin empresa).

**Primera vez / BD vacía:** `php artisan migrate --seed` (local) o `db:seed --force` en Cloud. Re-ejecutar el seed es seguro (`firstOrCreate`). Si falta seed con flag demo activo, `/login` muestra `MESSAGE_DEMO_SEED_MISSING` (texto para usuario final) y en `local` también `MESSAGE_DEMO_SEED_MISSING_DEV_HINT`; el mismo texto en `demoRole` si intentan enviar sin datos.

**Antes de go-live real:** `AVICORE_DEMO_LOGIN=false` y redeploy.

### Rol → pantalla tras login

| Perfil en selector | Destino | Uso en desarrollo |
|--------------------|---------|-------------------|
| **Dueño** | `/admin` | **Recomendado** para probar panel admin (estructura, usuarios, futuro dashboard) |
| Administrativo | `/admin` | Mismo panel que Dueño en MVP; no hace falta probar ambos en cada tarea |
| Encargado | `/admin` | Supervisión; sin CRUD usuarios ni estructura |
| Operario | `/operario` | Carga en galpón |
| Admin AviCore | `/admin` | Soporte multiempresa (sin `empresa_id`) |
