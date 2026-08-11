// Tema claro/oscuro del portal. Cargar antes de site.js.

const PortalTheme = (() => {
  const THEME_KEY = "portal-theme";

  function resolveTheme() {
    const stored = localStorage.getItem(THEME_KEY);
    if (stored === "light" || stored === "dark") return stored;
    return window.matchMedia("(prefers-color-scheme: light)").matches ? "light" : "dark";
  }

  function updateThemeButton(btnTheme, theme) {
    if (!btnTheme) return;
    const isLight = theme === "light";
    btnTheme.textContent = isLight ? "Oscuro" : "Claro";
    btnTheme.setAttribute("aria-label", isLight ? "Activar modo oscuro" : "Activar modo claro");
    btnTheme.setAttribute("title", isLight ? "Modo oscuro" : "Modo claro");
  }

  function applyTheme(theme, btnTheme) {
    const resolved = theme === "light" ? "light" : "dark";
    document.documentElement.setAttribute("data-theme", resolved);
    localStorage.setItem(THEME_KEY, resolved);
    updateThemeButton(btnTheme, resolved);
  }

  function toggleTheme(btnTheme) {
    const current = document.documentElement.getAttribute("data-theme") || "dark";
    applyTheme(current === "light" ? "dark" : "light", btnTheme);
  }

  return { resolveTheme, applyTheme, toggleTheme };
})();
