const OPERARIO_SHELL_SELECTOR = '.avicore-operario-shell';
const HOME_NAV_SELECTOR = '.avicore-home-nav';
const HOME_NAV_SCROLL_THRESHOLD_PX = 12;

function setOperarioNavigating(isNavigating) {
    document
        .querySelector(OPERARIO_SHELL_SELECTOR)
        ?.classList.toggle('avicore-operario-shell--navigating', isNavigating);
}

function syncHomeNavScrollState() {
    const nav = document.querySelector(HOME_NAV_SELECTOR);

    if (!nav) {
        return;
    }

    nav.classList.toggle(
        'avicore-home-nav--content-under',
        window.scrollY > HOME_NAV_SCROLL_THRESHOLD_PX,
    );
}

function bindHomeNavScrollState() {
    syncHomeNavScrollState();

    if (window.__avicoreHomeNavScrollBound) {
        return;
    }

    window.__avicoreHomeNavScrollBound = true;
    window.addEventListener('scroll', syncHomeNavScrollState, { passive: true });
}

document.addEventListener('livewire:navigating', () => {
    setOperarioNavigating(true);
});

document.addEventListener('livewire:navigated', () => {
    setOperarioNavigating(false);
    bindHomeNavScrollState();
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindHomeNavScrollState);
} else {
    bindHomeNavScrollState();
}
