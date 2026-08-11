// Runtime portal. NAV_SECTIONS: site.nav.js · PortalTheme · PortalToc (cargar antes).

const DEFAULT_PAGE = "inicio";
const SITE_TITLE = "Portal AviCore";

const sidebarOverlay = document.getElementById("sidebar-overlay");
const btnMenu = document.getElementById("btn-menu");
const btnTheme = document.getElementById("btn-theme");
const visor = document.getElementById("visor");
const contentArea = document.getElementById("contenido");
const navRoot = document.getElementById("lista-modulos");
const homeLink = document.getElementById("home-link");
const pageTitle = document.getElementById("page-title");
const btnPrint = document.getElementById("btn-print");
const tocSidebar = document.getElementById("toc-sidebar");
const tocList = document.getElementById("toc-list");

let activeSectionIndex = 0;

function isMobile() {
  return window.matchMedia("(max-width: 768px)").matches;
}

function setMenuOpen(open) {
  document.body.classList.toggle("menu-open", open);
  btnMenu?.setAttribute("aria-expanded", open ? "true" : "false");
  if (sidebarOverlay) sidebarOverlay.hidden = !open;
}

function resolveNavHighlight(id) {
  if (id === "dev-plantillas-cursor" || id === "dev-plantillas-chatgpt") {
    return "dev-plantillas";
  }
  return id;
}

function setActiveNav(id) {
  const highlightId = resolveNavHighlight(id);
  navRoot?.querySelectorAll(".nav-item[data-page-id], a.nav-item").forEach((el) => {
    el.classList.toggle("active", el.dataset.pageId === highlightId);
  });

  const sectionIndex = findSectionIndexForPage(id);
  if (sectionIndex >= 0) openSectionGroup(sectionIndex, false);
}

function findSectionIndexForPage(id) {
  return NAV_SECTIONS.findIndex((section) => section.items.some((item) => item.id === id));
}

function openSectionGroup(sectionIndex, rerender = true) {
  if (sectionIndex < 0 || sectionIndex >= NAV_SECTIONS.length) return;

  activeSectionIndex = sectionIndex;

  if (!rerender) {
    navRoot?.querySelectorAll(".nav-group").forEach((group) => {
      const index = Number(group.dataset.sectionIndex);
      const isOpen = index === sectionIndex;
      group.classList.toggle("collapsed", !isOpen);
      group.querySelector(".nav-group-header")?.setAttribute("aria-expanded", isOpen ? "true" : "false");
    });
    return;
  }

  renderNav();
}

async function copyText(text, btn, labelDefault = "Copiar") {
  try {
    await navigator.clipboard.writeText(text);
    btn.textContent = "Copiado";
    btn.classList.add("is-copied");
  } catch {
    btn.textContent = "Error";
    btn.classList.add("is-error");
  }

  window.setTimeout(() => {
    btn.textContent = labelDefault;
    btn.classList.remove("is-copied", "is-error");
  }, 2000);
}

function attachCodeCopyButtons(container) {
  container?.querySelectorAll("pre > code").forEach((code) => {
    const pre = code.parentElement;
    if (!pre || pre.querySelector(".code-copy-btn")) return;
    if (pre.closest("details:not(.template-guide)")) return;

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "code-copy-btn";
    btn.setAttribute("aria-label", "Copiar código");
    btn.textContent = "Copiar";
    pre.appendChild(btn);

    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      const text = code.textContent.replace(/\r\n/g, "\n").trim();
      await copyText(text, btn, "Copiar");
    });
  });
}

function attachAccordionToolbar(container) {
  if (!container) return;
  const accordion = container.querySelector("[data-accordion]");
  if (!accordion) return;

  container.querySelectorAll("[data-accordion-expand]").forEach((btn) => {
    btn.addEventListener("click", () => {
      accordion.querySelectorAll("details").forEach((d) => {
        d.open = true;
      });
    });
  });
  container.querySelectorAll("[data-accordion-collapse]").forEach((btn) => {
    btn.addEventListener("click", () => {
      accordion.querySelectorAll("details").forEach((d) => {
        d.open = false;
      });
    });
  });
}

function attachPortalGoto(container) {
  container?.querySelectorAll("[data-portal-page][data-portal-href]").forEach((el) => {
    if (el.dataset.bound) return;
    el.dataset.bound = "1";
    el.addEventListener("click", (e) => {
      e.preventDefault();
      loadPage(el.dataset.portalPage, el.dataset.portalHref, el.dataset.portalLabel || "");
    });
  });
}

function attachTemplateCopyButtons(container) {
  if (!container) return;

  container.querySelectorAll("details:not(.template-guide)").forEach((details) => {
    const codes = details.querySelectorAll("pre > code");
    if (!codes.length) return;

    const summary = details.querySelector(":scope > summary");
    if (!summary || summary.querySelector(".template-copy-btn")) return;

    const title = summary.textContent.trim();
    summary.textContent = "";

    const row = document.createElement("div");
    row.className = "template-summary-row";

    const titleEl = document.createElement("span");
    titleEl.className = "template-summary-row__title";
    titleEl.textContent = title;

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "template-copy-btn";
    btn.setAttribute("aria-label", `Copiar template: ${title}`);
    btn.textContent = "Copiar";

    row.append(titleEl, btn);
    summary.appendChild(row);

    btn.addEventListener("click", async (e) => {
      e.preventDefault();
      e.stopPropagation();

      const text = [...codes]
        .map((code) => code.textContent.replace(/\r\n/g, "\n").trim())
        .filter(Boolean)
        .join("\n\n");

      await copyText(text, btn, "Copiar");
    });
  });
}

function injectPlantillaSubnav(article) {
  const host = article.querySelector("[data-plantilla-nav]");
  if (!host || typeof PLANTILLA_NAV === "undefined") return;

  const current = host.getAttribute("data-plantilla-nav");
  host.className = "doc-subnav";
  host.setAttribute("role", "navigation");
  host.setAttribute("aria-label", "Secciones plantillas");

  const links = PLANTILLA_NAV.map((item) => {
    const isCurrent = item.id === current;
    return `<button type="button" class="portal-inline-link${isCurrent ? " is-current" : ""}" data-portal-page="${item.id}" data-portal-href="${item.href}" data-portal-label="${item.label}">${item.short}</button>`;
  }).join('<span class="doc-subnav__sep" aria-hidden="true">·</span>');

  host.innerHTML = `<span class="doc-subnav__label">Sección:</span> ${links}`;
}

function replayFadeIn() {
  if (!visor) return;
  visor.classList.remove("fade-in");
  void visor.offsetWidth;
  visor.classList.add("fade-in");
}

function enhancePage() {
  if (!visor) return;

  const article = document.createElement("article");
  article.className = "doc-page";
  article.innerHTML = visor.innerHTML;

  PortalToc.assignHeadingIds([...article.querySelectorAll("h2")], "h2");
  PortalToc.assignHeadingIds([...article.querySelectorAll("h3")], "section");

  injectPlantillaSubnav(article);
  attachTemplateCopyButtons(article);
  attachAccordionToolbar(article);
  attachPortalGoto(article);
  attachCodeCopyButtons(article);

  visor.innerHTML = "";
  visor.appendChild(article);
  PortalToc.updateToC(article, contentArea, tocSidebar, tocList);
  replayFadeIn();
  contentArea?.scrollTo(0, 0);
}

function renderNavItem(item) {
  if (item.pending || !item.href) {
    return `<li class="nav-item--pending">${item.label}</li>`;
  }

  const tag = item.printable ? ' <span class="nav-item__tag">· imprimible</span>' : "";

  return `<li class="nav-list-item">
    <button type="button" class="nav-item" data-page-id="${item.id}" data-href="${item.href}" data-label="${item.label}">${item.label}${tag}</button>
  </li>`;
}

function attachNavItemHandlers(container) {
  container?.querySelectorAll("button[data-href]").forEach((btn) => {
    btn.addEventListener("click", () => {
      loadPage(btn.dataset.pageId, btn.dataset.href, btn.dataset.label);
    });
  });
}

function attachCategoryHandlers() {
  navRoot?.querySelectorAll(".nav-group-header").forEach((btn) => {
    btn.addEventListener("click", () => {
      const group = btn.closest(".nav-group");
      const index = Number(group?.dataset.sectionIndex);
      if (Number.isNaN(index)) return;

      const wasCollapsed = group.classList.contains("collapsed");
      navRoot.querySelectorAll(".nav-group").forEach((node) => {
        node.classList.add("collapsed");
        node.querySelector(".nav-group-header")?.setAttribute("aria-expanded", "false");
      });

      if (wasCollapsed) {
        group.classList.remove("collapsed");
        btn.setAttribute("aria-expanded", "true");
        activeSectionIndex = index;
      }
    });
  });
}

function renderNavTree() {
  const groups = NAV_SECTIONS.map((section, index) => {
    const isOpen = index === activeSectionIndex;
    const items = section.items.map((item) => renderNavItem(item)).join("");

    return `<li class="nav-group${isOpen ? "" : " collapsed"}" data-section-index="${index}">
      <button
        type="button"
        class="nav-group-header"
        aria-expanded="${isOpen ? "true" : "false"}"
      >
        <span class="nav-group-header__label">${section.title}</span>
        <span class="nav-group-header__count">${section.items.length}</span>
      </button>
      <div class="nav-group-content">
        <div class="nav-group-wrapper">
          <ul class="nav-group-items">${items}</ul>
        </div>
      </div>
    </li>`;
  }).join("");

  return `<ul class="nav-tree" aria-label="Categorías">${groups}</ul>`;
}

function renderNav() {
  if (!navRoot) return;
  navRoot.innerHTML = renderNavTree();
  attachCategoryHandlers();
  attachNavItemHandlers(navRoot);
}

function updateTopBarTitle(title) {
  const resolved = title || SITE_TITLE;
  if (pageTitle) pageTitle.textContent = resolved;
  document.title = title ? `${title} · AviCore` : SITE_TITLE;
}

async function loadPage(id, href, label) {
  if (!visor || !id || !href) return;

  PortalToc.clearToc(contentArea, tocSidebar, tocList);
  setActiveNav(id);
  visor.classList.add("is-loading");
  visor.textContent = "Cargando…";
  if (isMobile()) setMenuOpen(false);

  try {
    const res = await fetch(href);
    if (!res.ok) throw new Error(String(res.status));
    visor.innerHTML = await res.text();
    enhancePage();
    visor.classList.remove("is-loading");

    const h1 = visor.querySelector("h1");
    const resolvedTitle = h1?.textContent.trim() || label || "";
    updateTopBarTitle(resolvedTitle);

    history.replaceState(null, "", `#${id}`);
    visor.focus({ preventScroll: true });
  } catch {
    visor.classList.remove("is-loading");
    visor.innerHTML = `<p class="error">No se pudo cargar <code>${href}</code>. Verificá que Live Server esté activo.</p>`;
    updateTopBarTitle(SITE_TITLE);
    replayFadeIn();
  }
}

function resolveInitial() {
  const hash = location.hash.slice(1);
  if (!hash) return { id: DEFAULT_PAGE, href: "contenido/inicio.html", label: "Inicio" };
  for (const s of NAV_SECTIONS) {
    for (const item of s.items) {
      if (item.id === hash && item.href) {
        return { id: item.id, href: item.href, label: item.label };
      }
    }
  }
  if (typeof PLANTILLA_NAV !== "undefined") {
    const plantilla = PLANTILLA_NAV.find((item) => item.id === hash);
    if (plantilla) {
      return { id: plantilla.id, href: plantilla.href, label: plantilla.label };
    }
  }
  return { id: DEFAULT_PAGE, href: "contenido/inicio.html", label: "Inicio" };
}

btnMenu?.addEventListener("click", () => {
  setMenuOpen(!document.body.classList.contains("menu-open"));
});

sidebarOverlay?.addEventListener("click", () => setMenuOpen(false));

homeLink?.addEventListener("click", (e) => {
  e.preventDefault();
  loadPage(DEFAULT_PAGE, "contenido/inicio.html", "Inicio");
});

btnPrint?.addEventListener("click", () => window.print());

btnTheme?.addEventListener("click", () => PortalTheme.toggleTheme(btnTheme));

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") setMenuOpen(false);
});

window.addEventListener("resize", () => {
  if (!isMobile()) setMenuOpen(false);
});

document.addEventListener("DOMContentLoaded", () => {
  PortalTheme.applyTheme(
    document.documentElement.getAttribute("data-theme") || PortalTheme.resolveTheme(),
    btnTheme,
  );
  const initial = resolveInitial();
  const initialSection = findSectionIndexForPage(initial.id);
  activeSectionIndex = initialSection >= 0 ? initialSection : 0;
  renderNav();
  loadPage(initial.id, initial.href, initial.label);
});
