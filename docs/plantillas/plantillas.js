const toast = document.getElementById("toast");
    let toastTimer;

    async function copyToClipboard(text) {
      if (navigator.clipboard && window.isSecureContext) {
        try {
          await navigator.clipboard.writeText(text);
          return true;
        } catch (_) {
          /* fallback abajo */
        }
      }
      const ta = document.createElement("textarea");
      ta.value = text;
      ta.setAttribute("readonly", "");
      ta.style.cssText = "position:fixed;top:0;left:0;width:2em;height:2em;opacity:0";
      document.body.appendChild(ta);
      ta.focus();
      ta.select();
      ta.setSelectionRange(0, text.length);
      let ok = false;
      try {
        ok = document.execCommand("copy");
      } catch (_) {
        ok = false;
      }
      document.body.removeChild(ta);
      return ok;
    }

    function showToast(message, isError) {
      toast.textContent = message;
      toast.style.background = isError ? "#991b1b" : "";
      toast.classList.add("show");
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => {
        toast.classList.remove("show");
        toast.style.background = "";
        toast.textContent = "Copiado al portapapeles";
      }, 2800);
    }

    async function handleCopy(btn, getText) {
      const text = getText().trim();
      if (!text) {
        showToast("No se encontró el texto del mensaje.", true);
        return;
      }
      const ok = await copyToClipboard(text);
      showToast(
        ok ? "Copiado al portapapeles" : "No se pudo copiar. Seleccioná el texto del recuadro gris y Ctrl+C.",
        !ok
      );
    }

    document.querySelectorAll(".btn-copy-header").forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        e.preventDefault();
        e.stopPropagation();
        const details = btn.closest("details");
        const pre = details?.querySelector("pre[data-copy]");
        await handleCopy(btn, () => pre?.textContent ?? "");
      });
    });

    document.querySelectorAll(".btn-copy").forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        e.preventDefault();
        const pre = btn.closest(".copy-row")?.previousElementSibling;
        const text = pre?.matches?.("pre[data-copy]") ? pre.textContent : "";
        await handleCopy(btn, () => text);
      });
    });

    document.getElementById("expand-all").onclick = () =>
      document.querySelectorAll("#accordion details").forEach((d) => (d.open = true));
    document.getElementById("collapse-all").onclick = () =>
      document.querySelectorAll("#accordion details").forEach((d) => (d.open = false));
