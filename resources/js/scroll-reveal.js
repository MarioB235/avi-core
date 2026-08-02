const REVEAL_SELECTOR = '[data-avicore-reveal]';
const VISIBLE_CLASS = 'avicore-reveal--visible';
const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';

let observer = null;

function prefersReducedMotion() {
    return window.matchMedia?.(REDUCED_MOTION_QUERY)?.matches ?? false;
}

function revealElement(element) {
    element.classList.add(VISIBLE_CLASS);
    observer?.unobserve(element);
}

function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    const viewHeight = window.innerHeight || document.documentElement.clientHeight;

    return rect.top < viewHeight * 0.92 && rect.bottom > 0;
}

function scan(root = document) {
    const scope = root instanceof Element ? root : document;
    const elements = scope.matches?.(REVEAL_SELECTOR)
        ? [scope, ...scope.querySelectorAll(REVEAL_SELECTOR)]
        : [...scope.querySelectorAll(REVEAL_SELECTOR)];

    elements.forEach((element) => {
        if (element.classList.contains(VISIBLE_CLASS)) {
            return;
        }

        if (prefersReducedMotion() || isInViewport(element)) {
            revealElement(element);

            return;
        }

        observer?.observe(element);
    });
}

function initScrollReveal() {
    if (prefersReducedMotion()) {
        document.querySelectorAll(REVEAL_SELECTOR).forEach(revealElement);

        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    revealElement(entry.target);
                }
            });
        },
        {
            root: null,
            rootMargin: '0px 0px -6% 0px',
            threshold: 0.12,
        },
    );

    scan();
}

function setupLivewireHooks() {
    document.addEventListener('livewire:navigated', () => {
        requestAnimationFrame(() => scan());
    });

    document.addEventListener('livewire:init', () => {
        if (!window.Livewire?.hook) {
            return;
        }

        Livewire.hook('morph.updated', ({ el }) => {
            if (el?.matches?.(REVEAL_SELECTOR) || el?.querySelector?.(REVEAL_SELECTOR)) {
                requestAnimationFrame(() => scan(el));
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollReveal);
} else {
    initScrollReveal();
}

setupLivewireHooks();

export function rescanAvicoreReveal(root = document) {
    scan(root);
}
