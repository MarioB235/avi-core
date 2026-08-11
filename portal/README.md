# Portal AviCore — documentación humana

Centro de lectura HTML para producto, mercado uruguayo, contexto, plantillas Cursor e imprimibles. El agente Cursor sigue usando `.cursor/skills/*/references/*.md`.

## Cómo abrirlo (importante)

Live Server usa la carpeta `portal/` como **raíz** (igual que el portal ATLAS del otro proyecto).

1. Instalar extensión **Live Server** en Cursor/VS Code.
2. Clic derecho en `portal/index.html` → **Open with Live Server**.
3. La URL correcta es **una de estas** (sin `/portal/` en la ruta):

   - `http://127.0.0.1:5500/`
   - `http://127.0.0.1:5500/index.html`

**Si ves `Cannot GET /portal/index.html`** → estás en la URL equivocada o hay otro servidor en el puerto 5500. Cierra Live Server, vuelve a abrir desde `portal/index.html` y usa la URL de arriba.

**Alternativa sin Live Server** (desde la raíz del repo):

```bash
pnpm run portal:dev
```

Abre `http://127.0.0.1:5500/` igual que Live Server.

## Estructura

```text
portal/
├── index.html
├── CHANGELOG.md            # Historial de contrato documental
├── assets/                 # Logo y assets del portal
├── css/
├── js/site.nav.js          # NAV_SECTIONS
├── js/site.theme.js        # PortalTheme (claro/oscuro)
├── js/site.toc.js          # PortalToc (índice lateral)
├── js/site.js
├── contenido/
│   └── desarrollo/         # Contexto, plantillas Cursor, changelog
├── imprimibles/
└── _sistema-documental/
```

## Agregar una página

1. HTML en `contenido/<categoria>/`.
2. Entrada en `js/site.nav.js`.
3. Si cambió contrato → skill `.md` + `portal/CHANGELOG.md`.
