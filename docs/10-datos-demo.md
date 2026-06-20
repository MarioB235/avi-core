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

- 1 empresa demo.
- 2 granjas.
- 4 galpones por granja.
- 8 galpones en total.
- Lotes activos.
- Algunos galpones con más de un lote.
- 30 días de registros.

---

## 4. Granjas sugeridas

- Granja Norte.
- Granja Sur.

---

## 5. Galpones sugeridos

### Granja Norte

- Galpón N1.
- Galpón N2.
- Galpón N3.
- Galpón N4.

### Granja Sur

- Galpón S1.
- Galpón S2.
- Galpón S3.
- Galpón S4.

---

## 6. Usuarios demo (auth — Bloque 2)

Cargados con `AvicoreAuthSeeder` (`php artisan db:seed`). Empresa: **Avícola Demo** (`DEMO`).

### Login rápido en local

Con `APP_ENV=local` y `AVICORE_DEMO_LOGIN=true` (ver `.env.example`), en `/login`:

- Documento: `000000000`
- Contraseña: `Avicore2026!`
- **Perfil demo:** selector de rol (resuelve al usuario seedeado correspondiente vía `DemoLoginService`).

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

Credenciales para probar login en local: [`reference/arranque-local.md`](reference/arranque-local.md) § «Datos de prueba (login)».

Los usuarios de granjas/galpones/lotes (sección 3) se cargarán en fases posteriores del plan; hoy solo existe el seed de auth.

---

## 7. Escenarios demo

1. Producción normal.
2. Baja producción.
3. Alta mortalidad.
4. Galpón sin carga.
5. Galpón con varios lotes.
6. Registro anulado.
7. Ajuste manual de aves vivas.
8. Reporte diario generado.
9. Dashboard en tiempo real.
10. Carga desde celular y actualización en PC.

---

## 8. Datos por 30 días

Debe incluir variaciones para que los gráficos se vean completos.

Ejemplo:

- Producción diaria distinta por galpón.
- Mortalidad baja en la mayoría.
- Mortalidad alta en un galpón.
- Un día sin carga en un galpón.
- Alimentación cada ciertos días.

---

## 9. Forma de carga

Se recomienda usar:

- Seeders.
- CSV.
- JSON.

---

## 10. Uso

Los datos demo sirven para:

- Probar desarrollo.
- Mostrar demos comerciales.
- Probar reportes.
- Probar tiempo real.
- Probar alertas.
