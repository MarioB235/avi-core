/**
 * Captura temprana de beforeinstallprompt (MDN: registrar antes del UI custom).
 */
let deferredPrompt = null;

export const PWA_INSTALL_READY = 'avicore:pwa-install-ready';
export const PWA_INSTALLED = 'avicore:pwa-installed';
export const PWA_INSTALL_SESSION_DISMISS_KEY = 'avicore-pwa-install-session-dismissed';
export const PWA_INSTALLED_AT_KEY = 'avicore-pwa-installed-at';

const LEGACY_LOCAL_STORAGE_KEYS = [
    'avicore-pwa-install-dismissed',
    'avicore-pwa-install-snooze-until',
];

export function isPwaInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.matchMedia('(display-mode: fullscreen)').matches
        || window.navigator.standalone === true;
}

export function isMobileDevice() {
    return window.matchMedia('(max-width: 768px)').matches
        || /android|iphone|ipad|ipod/i.test(window.navigator.userAgent);
}

export function detectIos() {
    return /iphone|ipad|ipod/i.test(window.navigator.userAgent)
        || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);
}

export function isDismissedThisSession() {
    try {
        return sessionStorage.getItem(PWA_INSTALL_SESSION_DISMISS_KEY) === '1';
    } catch {
        return false;
    }
}

export function dismissThisSession() {
    try {
        sessionStorage.setItem(PWA_INSTALL_SESSION_DISMISS_KEY, '1');
    } catch {
        // Ignorar.
    }
}

export function clearPwaInstallSessionDismiss() {
    try {
        sessionStorage.removeItem(PWA_INSTALL_SESSION_DISMISS_KEY);
    } catch {
        // Ignorar.
    }
}

export function clearLegacyPwaInstallDismissKeys() {
    try {
        for (const key of LEGACY_LOCAL_STORAGE_KEYS) {
            localStorage.removeItem(key);
        }
    } catch {
        // Ignorar.
    }
}

export function shouldShowBanner() {
    return isMobileDevice() && ! isPwaInstalled() && ! isDismissedThisSession();
}

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredPrompt = event;
    window.dispatchEvent(new CustomEvent(PWA_INSTALL_READY));
});

function recordInstallMetric() {
    const installedAt = new Date().toISOString();

    try {
        sessionStorage.setItem(PWA_INSTALLED_AT_KEY, installedAt);
    } catch {
        // Ignorar.
    }

    return installedAt;
}

window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    const installedAt = recordInstallMetric();

    window.dispatchEvent(new CustomEvent(PWA_INSTALLED, {
        detail: { installedAt },
    }));
});

window.__avicorePwaInstall = {
    hasPrompt() {
        return deferredPrompt !== null;
    },

    isInstalled: isPwaInstalled,

    isMobile: isMobileDevice,

    detectIos,

    isDismissedThisSession,

    dismissThisSession,

    clearSessionDismiss: clearPwaInstallSessionDismiss,

    clearLegacyDismissKeys: clearLegacyPwaInstallDismissKeys,

    shouldShowBanner,

    shouldShowMenuItem() {
        return isMobileDevice() && ! isPwaInstalled();
    },

    getInstalledAt() {
        try {
            return sessionStorage.getItem(PWA_INSTALLED_AT_KEY);
        } catch {
            return null;
        }
    },

    async prompt() {
        if (! deferredPrompt) {
            return null;
        }

        await deferredPrompt.prompt();
        const choice = await deferredPrompt.userChoice;
        deferredPrompt = null;

        return choice;
    },

    async offerInstall() {
        if (this.hasPrompt()) {
            return await this.prompt();
        }

        if (detectIos()) {
            window.dispatchEvent(new CustomEvent('snackbar-show', {
                detail: {
                    message: 'En Safari: Compartir → Añadir a pantalla de inicio.',
                    variant: 'info',
                },
            }));
        }

        return null;
    },
};

function maybeClearSessionDismissOnLogin() {
    if (document.body?.classList.contains('avicore-pwa-clear-session-dismiss')) {
        clearPwaInstallSessionDismiss();
        clearLegacyPwaInstallDismissKeys();
    }
}

if (document.body) {
    maybeClearSessionDismissOnLogin();
} else {
    document.addEventListener('DOMContentLoaded', maybeClearSessionDismissOnLogin);
}
