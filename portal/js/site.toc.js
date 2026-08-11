// TOC lateral del portal. Cargar antes de site.js.

const PortalToc = (() => {
  let tocScrollHandler = null;
  let tocScrollRaf = 0;

  function slugify(text) {
    return text
      .toLowerCase()
      .normalize("NFD")
      .replace(/\p{Diacritic}/gu, "")
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-|-$/g, "");
  }

  function assignHeadingIds(headings, idPrefix) {
    const usedIds = new Set();
    headings.forEach((heading, index) => {
      let id = heading.id;
      if (!id && idPrefix === "section") {
        id = `${idPrefix}-${index}`;
      } else if (!id) {
        id = slugify(heading.textContent) || `${idPrefix}-${index}`;
      }
      while (usedIds.has(id)) id = `${id}-${index}`;
      heading.id = id;
      usedIds.add(id);
    });
  }

  function getTocScrollOffset() {
    const topbar = document.querySelector(".top-bar");
    return (topbar?.offsetHeight ?? 56) + 16;
  }

  function getHeadingTopInContainer(heading, container) {
    const containerRect = container.getBoundingClientRect();
    const headingRect = heading.getBoundingClientRect();
    return container.scrollTop + headingRect.top - containerRect.top;
  }

  function collectTocHeadings(article) {
    const h2s = [...article.querySelectorAll("h2")];
    if (h2s.length >= 2) return h2s;

    const h3s = [...article.querySelectorAll("h3")].filter((h) => !h.closest("details"));
    return h3s.length >= 2 ? h3s : [];
  }

  function syncTocActive(headings, links, contentArea, tocSidebar) {
    if (!contentArea || headings.length === 0) return;

    const scrollPos = contentArea.scrollTop + getTocScrollOffset();
    let activeId = headings[0].id;

    for (const heading of headings) {
      if (getHeadingTopInContainer(heading, contentArea) <= scrollPos + 4) {
        activeId = heading.id;
      } else {
        break;
      }
    }

    let activeLink = null;
    links.forEach((link) => {
      const isActive = link.getAttribute("href") === `#${activeId}`;
      link.classList.toggle("active", isActive);
      if (isActive) activeLink = link;
    });

    if (activeLink && tocSidebar) {
      const linkTop = activeLink.offsetTop;
      const linkBottom = linkTop + activeLink.offsetHeight;
      const viewTop = tocSidebar.scrollTop;
      const viewBottom = viewTop + tocSidebar.clientHeight;
      if (linkTop < viewTop + 24) {
        tocSidebar.scrollTop = Math.max(0, linkTop - 24);
      } else if (linkBottom > viewBottom - 24) {
        tocSidebar.scrollTop = linkBottom - tocSidebar.clientHeight + 24;
      }
    }
  }

  function scrollToHeading(heading, contentArea) {
    if (!contentArea) return;
    const target = getHeadingTopInContainer(heading, contentArea) - getTocScrollOffset();
    contentArea.scrollTo({ top: Math.max(0, target), behavior: "smooth" });
  }

  function clearToc(contentArea, tocSidebar, tocList) {
    if (tocScrollHandler && contentArea) {
      contentArea.removeEventListener("scroll", tocScrollHandler);
    }
    tocScrollHandler = null;
    cancelAnimationFrame(tocScrollRaf);
    tocScrollRaf = 0;
    if (tocList) tocList.innerHTML = "";
    if (tocSidebar) tocSidebar.hidden = true;
  }

  function updateToC(article, contentArea, tocSidebar, tocList) {
    clearToc(contentArea, tocSidebar, tocList);
    if (!article || !tocList || !tocSidebar) return;

    const headers = collectTocHeadings(article);
    if (headers.length < 2) return;

    const links = [];
    headers.forEach((heading) => {
      const link = document.createElement("a");
      link.className = "toc-link";
      if (heading.tagName === "H3") link.classList.add("toc-link--sub");
      link.href = `#${heading.id}`;
      link.textContent = heading.textContent.trim();
      link.addEventListener("click", (e) => {
        e.preventDefault();
        scrollToHeading(heading, contentArea);
        history.replaceState(null, "", `#${heading.id}`);
        syncTocActive(headers, links, contentArea, tocSidebar);
      });
      tocList.appendChild(link);
      links.push(link);
    });

    tocSidebar.hidden = false;

    tocScrollHandler = () => {
      cancelAnimationFrame(tocScrollRaf);
      tocScrollRaf = requestAnimationFrame(() => syncTocActive(headers, links, contentArea, tocSidebar));
    };
    contentArea?.addEventListener("scroll", tocScrollHandler, { passive: true });
    syncTocActive(headers, links, contentArea, tocSidebar);
  }

  return { slugify, assignHeadingIds, clearToc, updateToC };
})();
