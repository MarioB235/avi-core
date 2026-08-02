import { registerSW } from 'virtual:pwa-register';

if ('serviceWorker' in navigator) {
    const updateSW = registerSW({
        immediate: true,
        onNeedRefresh() {
            window.__avicorePwaUpdate = () => updateSW(true);

            window.dispatchEvent(new CustomEvent('snackbar-show', {
                detail: {
                    message: 'Hay una nueva versión de AviCore.',
                    variant: 'info',
                    actionLabel: 'Actualizar',
                    actionKey: 'pwa-update',
                },
            }));
        },
    });
}
