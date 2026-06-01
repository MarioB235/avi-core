# Referencia — Arranque local

**Fuente maestra del entorno de desarrollo en PC.**  
Stack y principios: [`07-arquitectura-tecnica.md`](../07-arquitectura-tecnica.md).

---

## Requisitos

- PHP 8.3+
- Composer
- Node.js y npm
- PostgreSQL (pgAdmin u otro cliente)
- Extensiones PHP: `pdo_pgsql`, `pgsql`

---

## Primera vez en el proyecto

Desde la raíz del repo (donde está `artisan`):

```bash
composer install
cp .env.example .env   # si no existe .env
php artisan key:generate
npm install
npm run build
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

Tablas: skeleton Laravel + `empresas` + `users` (esquema AviCore). Ver [`estructura-base-datos.md`](estructura-base-datos.md).

### Datos de prueba (login)

Usuarios y contraseñas demo: [`10-datos-demo.md`](../10-datos-demo.md) § 6. Tras migrar, cargarlos con:

```bash
php artisan db:seed
```

| Perfil | Documento | Contraseña | Notas |
|--------|-----------|------------|--------|
| Admin AviCore | `900000001` | `Avicore2026!` | Panel `/admin` |
| Dueño demo | `100000001` | `Avicore2026!` | Empresa «Avícola Demo» |
| Administrativo demo | `300000001` | `Avicore2026!` | Panel `/admin` |
| Encargado demo | `400000001` | `Avicore2026!` | Panel `/admin` |
| Operario demo | `200000001` | `Temporal2026!` tras seed; si ya cambió en tu PC: `Actual2026!` | Primer ingreso obliga cambio; luego `/operario` |

Rutas: `/login`, `/password/change`, `/admin`, `/operario`.

---

## Servidor de desarrollo

En local hacen falta **dos procesos** (dos terminales). No son dos “versiones” de la app.

| URL | Qué es | ¿Abrís el navegador ahí? |
|-----|--------|---------------------------|
| `http://127.0.0.1:8000` o `http://localhost:8000` | **Laravel** (`php artisan serve`) — pantallas, login, API | **Sí** — esta es AviCore |
| `http://localhost:5173` | **Vite** (`npm run dev` / `pnpm run dev`) — compila CSS/JS en caliente | **No** — verás solo la página informativa de Vite |

La app carga estilos y scripts desde Vite en segundo plano cuando abrís el puerto **8000**. Si cerrás la terminal de Vite, la app puede verse sin estilos o sin recarga automática; el login y las rutas siguen en **8000**.

**Windows — pantalla sin estilos (elementos amontonados):** Vite puede escribir `public/hot` con `[::1]:5173` (IPv6) y el navegador no carga el CSS. En `vite.config.js` está `server.host: '127.0.0.1'`. Tras cambiar eso: detené `composer dev`, volvé a arrancarlo y recargá con Ctrl+F5. Si persiste, borrá `public/hot`, ejecutá `npm run build` y recargá (usa assets compilados sin Vite en caliente).

Terminal 1:

```bash
php artisan serve
```

Terminal 2 (assets en caliente; dejarla abierta):

```bash
npm run dev
```

(o `pnpm run dev` si usás pnpm)

**Entrada recomendada:** `http://localhost:8000` o `http://localhost:8000/login` — la raíz `/` redirige al login si no hay sesión.

Usuarios demo: tabla de la sección «Datos de prueba (login)» más arriba.

Previews de layout (requieren sesión): `/dev/admin-layout`, `/dev/operario-layout`.

Atajo Composer (servidor + cola + Vite vía `npx concurrently`; logs en vivo con Pail solo en Linux/macOS):

```bash
composer dev
```

En **Windows**, Pail no corre (requiere `pcntl`); el resto sí. Los logs quedan en `storage/logs/laravel.log`. Alternativa manual: dos terminales con `php artisan serve` y `npm run dev` (tabla más arriba).

Artefactos de fuentes en desarrollo (`public/fonts-manifest.dev.json`) se generan con Vite y **no** se versionan (ver `.gitignore`).

**Assets de marca:** tras reemplazar fondos en `resources/images/brand/`, ejecutar `python scripts/optimize-brand-assets.py` (requiere Pillow: `pip install pillow`).

---

## Verificación

```bash
php artisan test
npm run build
```

---

## Secretos

- `.env` no se sube a Git (ver `.gitignore`).
- `.env.example` documenta variables sin valores secretos.
