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

### Contraseña y pgAdmin

- pgAdmin puede **guardar** la contraseña del servidor; Laravel **no** la lee desde pgAdmin.
- Si `php artisan migrate` falla con `no password supplied`, definir contraseña en pgAdmin: **Login/Group Roles** → **postgres** → **Properties** → **Definition** → **Password** → **Save**, y la misma en `.env`.
- Alternativa SQL (Query Tool): `ALTER USER postgres WITH PASSWORD 'tu_clave';`

El rol `postgres` es del **servidor**, no de una base concreta; puede usarse en varias bases según permisos.

### Migraciones Laravel (base)

```bash
php artisan migrate
```

Tablas iniciales del skeleton: `users`, `cache`, `jobs`, `migrations`, etc. Las tablas de negocio AviCore se agregan en módulos posteriores (ver [`estructura-base-datos.md`](estructura-base-datos.md)).

---

## Servidor de desarrollo

Terminal 1:

```bash
php artisan serve
```

Terminal 2 (assets en caliente):

```bash
npm run dev
```

URL: `http://localhost:8000`

Rutas de preview del Bloque 1: `/`, `/dev/admin-layout`, `/dev/operario-layout`.

Atajo Composer (servidor + cola + logs + Vite):

```bash
composer dev
```

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
