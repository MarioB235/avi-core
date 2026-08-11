# Referencia — Arranque local

**Fuente maestra del entorno de desarrollo en PC.**  
Stack y principios: [`arquitectura.md`](arquitectura.md).

---

## Requisitos

- PHP 8.3+
- Composer
- Node.js **22** y pnpm 10.x (misma major que CI y Laravel Cloud; Corepack: `corepack enable` si hace falta)
- PostgreSQL (pgAdmin u otro cliente)
- Extensiones PHP: `pdo_pgsql`, `pgsql`

---

## Primera vez en el proyecto

Atajo (migrate + seed + build): desde la raíz del repo (donde está `artisan`):

```bash
composer setup
```

Paso a paso equivalente:

```bash
composer install
cp .env.example .env   # si no existe .env
php artisan key:generate
pnpm install
php artisan migrate
php artisan db:seed
pnpm run build
```

---

## PostgreSQL

### Base de datos

Crear una base llamada **`avicore`** (pgAdmin: **Databases** → **Create** → **Database**, owner `postgres`).

### Variables en `.env`

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=avicore
DB_USERNAME=postgres
DB_PASSWORD=
```

Completar `DB_PASSWORD` con la contraseña del rol `postgres`.

### Contacto de soporte (login — MVP)

Opcional en `.env` (valores demo en `.env.example`). Alimentan el diálogo «¿Olvidaste tu contraseña?» vía `config/avicore.php` y `SupportContactService`:

```env
AVICORE_SUPPORT_WHATSAPP="+5491123456789"
AVICORE_SUPPORT_WHATSAPP_DISPLAY="+54 9 11 2345-6789"
AVICORE_SUPPORT_EMAIL="soporte@avicore.com"
```

WhatsApp requiere dígitos válidos; el correo debe pasar `FILTER_VALIDATE_EMAIL`. Si ambos fallan, el diálogo muestra mensaje genérico sin enlaces rotos.

### PWA (instalación móvil — MVP)

Opcional en `.env` (defaults en `.env.example`). Controlan manifest, service worker y banner «Instalar» vía `config/avicore.php`:

```env
AVICORE_PWA_ENABLED=true
AVICORE_PWA_INSTALL_PROMPT=true
```

`AVICORE_PWA_INSTALL_PROMPT=false` oculta el banner pero mantiene manifest/SW. Requiere `pnpm run build` y HTTPS para probar instalación en celular. Detalle: `avicore-pwa/references/pwa.md`.

### Base de datos para tests (`php artisan test`)

Los tests usan **PostgreSQL** (misma extensión `pdo_pgsql` que la app; no SQLite). Crear una base separada **`avicore_test`** (pgAdmin: **Create** → **Database**, owner `postgres`).

Copiar el entorno de prueba y completar la clave:

```bash
cp .env.testing.example .env.testing
```

En `.env.testing`, usar la misma `DB_PASSWORD` que en `.env`. Los tests ejecutan migraciones sobre `avicore_test` con `RefreshDatabase`; no mezclan datos con la base `avicore` de desarrollo.

### Contraseña y pgAdmin

- pgAdmin puede **guardar** la contraseña del servidor; Laravel **no** la lee desde pgAdmin.
- Si `php artisan migrate` falla con `no password supplied`, definir contraseña en pgAdmin: **Login/Group Roles** → **postgres** → **Properties** → **Definition** → **Password** → **Save**, y la misma en `.env`.
- Alternativa SQL (Query Tool): `ALTER USER postgres WITH PASSWORD 'tu_clave';`

El rol `postgres` es del **servidor**, no de una base concreta; puede usarse en varias bases según permisos.

### Migraciones Laravel (base)

```bash
php artisan migrate
```

Tablas: skeleton Laravel + esquema AviCore (`empresas`, `users`, `granjas`, `galpones`, `lotes`, `registros_operativos`, `vacunaciones`). Detalle: [`esquema-bd.md`](../../avicore-modelo-datos/references/esquema-bd.md).

Rutas útiles tras seed: `/login`, `/password/change`, `/admin`, `/admin/usuarios`, `/operario`.

### Datos de prueba (login)

**Credenciales del usuario único** (tras `db:seed`):

```text
Documento:  000000000
Contraseña: Avicore2026!
```

Detalle: [`demo.md`](../../avicore-datos-demo/references/demo.md) § 4. `composer setup` ya ejecuta seed; si migraste a mano:

```bash
php artisan db:seed
```

Re-ejecutar el seed es seguro (`AvicoreAuthSeeder` usa `firstOrCreate`).

**Modo demo MVP** (`AVICORE_DEMO_LOGIN=true`): un solo usuario (`000000000`). Elegí **Perfil** en el selector; el rol se aplica al entrar. Sin documento ni contraseña en pantalla.

**Login normal** (`AVICORE_DEMO_LOGIN=false`): documento `000000000` + `Avicore2026!`.

| Perfil en el select | Después del login |
|--------|-------------------|
| Admin AviCore | `/admin` |
| Dueño, Administrativo, Encargado | `/admin` |
| Operario | `/operario` |

Desactivar con `AVICORE_DEMO_LOGIN=false`: login con `000000000` + `Avicore2026!`.

---

## Servidor de desarrollo

En local hacen falta **dos procesos** (dos terminales). No son dos “versiones” de la app.

| URL | Qué es | ¿Abrís el navegador ahí? |
|-----|--------|---------------------------|
| `http://127.0.0.1:8000` o `http://localhost:8000` | **Laravel** (`php artisan serve`) — pantallas, login, API | **Sí** — esta es AviCore |
| `http://localhost:5173` | **Vite** (`pnpm run dev`) — compila CSS/JS en caliente | **No** — verás solo la página informativa de Vite |

La app carga estilos y scripts desde Vite en segundo plano cuando abrís el puerto **8000**. Si cerrás la terminal de Vite, la app puede verse sin estilos o sin recarga automática; el login y las rutas siguen en **8000**.

**Windows — pantalla sin estilos (elementos amontonados):** Vite puede escribir `public/hot` con `[::1]:5173` (IPv6) y el navegador no carga el CSS. En `vite.config.js` está `server.host: '127.0.0.1'`. Tras cambiar eso: detené `composer dev`, volvé a arrancarlo y recargá con Ctrl+F5. Si persiste, borrá `public/hot`, ejecutá `pnpm run build` y recargá (usa assets compilados sin Vite en caliente).

Terminal 1:

```bash
php artisan serve
```

Terminal 2 (assets en caliente; dejarla abierta):

```bash
pnpm run dev
```

**Entrada recomendada:** `http://localhost:8000` o `http://localhost:8000/login` — la raíz `/` redirige al login si no hay sesión.

Usuarios demo: tabla de la sección «Datos de prueba (login)» más arriba.

Previews de layout (requieren sesión): `/dev/admin-layout`, `/dev/operario-layout`.

Atajo Composer (servidor + cola + Vite vía `pnpm exec concurrently`; logs en vivo con Pail solo en Linux/macOS):

```bash
composer dev
```

En **Windows**, Pail no corre (requiere `pcntl`); el resto sí. Los logs quedan en `storage/logs/laravel.log`. Alternativa manual: dos terminales con `php artisan serve` y `pnpm run dev` (tabla más arriba).

Artefactos de fuentes en desarrollo (`public/fonts-manifest.dev.json`) se generan con Vite y **no** se versionan (ver `.gitignore`).

**Assets de marca:** tras reemplazar fondos en `resources/images/brand/`, ejecutar `python scripts/optimize-brand-assets.py` (requiere Pillow: `pip install pillow`).

---

## Verificación

```bash
php artisan test
pnpm run build
```

---

## Portal documental (HTML)

Lectura humana de producto y mercado (sin Laravel):

1. Extensión **Live Server** en VS Code/Cursor (root `/portal` en `.vscode/settings.json`, igual que ATLAS).
2. Clic derecho en `portal/index.html` → **Open with Live Server**.
3. URL correcta: `http://127.0.0.1:5500/` o `http://127.0.0.1:5500/index.html` (**sin** `/portal/` en la ruta).
4. Alternativa: `pnpm run portal:dev` desde la raíz del repo.

Si ves `Cannot GET /portal/index.html`, la URL es incorrecta o el puerto 5500 tiene otro servidor. Cerrar Live Server y reabrir desde `portal/index.html`.

Detalle: [`portal/README.md`](../../../../portal/README.md). Los `.md` en `.cursor/skills/` siguen siendo fuente del agente Cursor.

---

## Secretos

- `.env` no se sube a Git (ver `.gitignore`).
- `.env.example` documenta variables sin valores secretos.

---

## Despliegue en Laravel Cloud

Antes del dashboard: `pnpm run check:cloud-readiness`. Guía por fases (primera vez): [`deploy-laravel-cloud.md`](deploy-laravel-cloud.md).
