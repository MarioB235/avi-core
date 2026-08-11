const OPERARIO_SHELL_SELECTOR = '.avicore-operario-shell';

function setOperarioNavigating(isNavigating) {
    document
        .querySelector(OPERARIO_SHELL_SELECTOR)
        ?.classList.toggle('avicore-operario-shell--navigating', isNavigating);
}

document.addEventListener('livewire:navigating', () => {
    setOperarioNavigating(true);
});

document.addEventListener('livewire:navigated', () => {
    setOperarioNavigating(false);
});
