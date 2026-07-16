# Referencia — Despliegue en Laravel Cloud

Guía para publicar AviCore en [Laravel Cloud](https://cloud.laravel.com) — plataforma oficial de deploy para aplicaciones Laravel.

Stack local: [`arranque-local.md`](arranque-local.md). Arquitectura: [`arquitectura.md`](arquitectura.md).

---

## Qué incluye el repo

AviCore **no requiere archivos de config en la raíz** para Laravel Cloud (a diferencia de Vercel). El deploy se configura en el dashboard.

| Elemento en repo | Rol |
|------------------|-----|
| `public/index.php` | Front controller estándar |
| `bootstrap/app.php` | `trustProxies(at: '*')` para HTTPS detrás del proxy de Cloud |
| Ruta `/up` | Health check post-deploy |
| `pnpm run build` | Compila assets Vite (fase de build en Cloud) |

---

## Requisitos previos

1. Cuenta en [cloud.laravel.com](https://cloud.laravel.com) (plan Starter: 1.er mes gratis + créditos de uso).
2. Repositorio GitHub conectado a Laravel Cloud.
3. `APP_KEY` generado localmente: `php artisan key:generate --show` (no commitear).

---

## Crear aplicación (dashboard)

1. **+ New application** → conectar GitHub → seleccionar `avi-core`.
2. **Application name:** `avicore` (o el que prefieras).
3. **Region:** la más cercana al equipo (ej. `us-east-1`, `us-east-2`). La base de datos debe estar en la **misma región** que el compute.
4. **Settings → General:**
   - PHP **8.3**
   - Node **20**
5. **Build commands** (AviCore usa pnpm — `packageManager` en `package.json`):

```bash
composer install --no-dev
corepack enable
pnpm install --frozen-lockfile
pnpm run build
```

6. **Deploy commands** (por defecto):

```bash
php artisan migrate --force
```

7. En el canvas del entorno → **Add database** → **Laravel Serverless Postgres** (misma región). Cloud inyecta automáticamente `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

8. **Create Application** y esperar el primer deploy.

---

## Variables de entorno (Environment → Settings → Environment Variables)

Cargar manualmente (las de BD las inyecta Cloud al adjuntar Postgres):

| Variable | Valor staging / pruebas |
|----------|-------------------------|
| `APP_NAME` | `AviCore` |
| `APP_ENV` | `staging` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Salida de `php artisan key:generate --show` |
| `APP_URL` | URL del entorno, ej. `https://avicore-staging.laravel.cloud` |
| `AVICORE_DEMO_LOGIN` | `false` |
| `AVICORE_SUPPORT_WHATSAPP` | Igual que `.env.example` |
| `AVICORE_SUPPORT_WHATSAPP_DISPLAY` | Igual que `.env.example` |
| `AVICORE_SUPPORT_EMAIL` | Igual que `.env.example` |

Opcionales recomendadas para staging:

| Variable | Valor |
|----------|-------|
| `SESSION_DRIVER` | `database` (tabla `sessions` ya existe) |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `sync` (MVP; colas managed en fase posterior) |

Tras cambiar variables → **redeploy** el entorno.

---

## Datos demo (primera vez)

Si la BD está vacía tras `migrate`, ejecutar en Laravel Cloud (comandos del entorno):

```bash
php artisan db:seed --force
```

Usuarios demo: ver [`demo.md`](../../avicore-datos-demo/references/demo.md) y sección login en [`arranque-local.md`](arranque-local.md). En Cloud **desactivar** el selector (`AVICORE_DEMO_LOGIN=false`) y usar documento + contraseña del seeder.

---

## Verificación post-deploy

1. `GET /up` → 200.
2. `/login` carga con estilos (`/build/assets/...`).
3. Login con usuario seedeado (ej. documento `100000001`, contraseña del seeder).
4. Flujo operario: `/operario`, selección galpón, carga huevos.
5. Imágenes de marca en `/images/brand/...`.

---

## Deploys siguientes

Por defecto, **push a la rama conectada** dispara deploy automático.

Deploy manual vía **Deploy hook** (Settings → Deployments): útil para CI o GitHub Actions.

```bash
curl -X POST "https://cloud.laravel.com/api/deploy/hooks/<tu-hook>?commit_hash=<sha>"
```

---

## Recursos futuros (fuera del MVP actual)

Laravel Cloud soporta nativamente (activar cuando el plan lo pida):

- **Colas managed** y workers en background.
- **Scheduler** (cron de Laravel).
- **Reverb / WebSockets** (Bloque 6 del plan AviCore).
- **Object storage** y dominios custom.

---

## Referencias oficiales

- [Laravel Cloud — documentación](https://cloud.laravel.com/docs)
- [Environments y variables](https://cloud.laravel.com/docs/environments)
- [Deployments](https://cloud.laravel.com/docs/deployments)
- [Serverless Postgres](https://cloud.laravel.com/docs/databases)
