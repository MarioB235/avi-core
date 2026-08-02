@if (config('avicore.pwa.install_prompt') && auth()->check())
    <div
        class="avicore-pwa-install"
        x-data="{
            visible: false,
            isIos: false,
            canInstall: false,
            showTimer: null,
            delayMs: 3000,
            pwa() {
                return window.__avicorePwaInstall;
            },
            init() {
                const install = this.pwa();

                if (! install) {
                    return;
                }

                install.clearLegacyDismissKeys();

                if (! install.shouldShowBanner()) {
                    return;
                }

                this.isIos = install.detectIos();

                window.addEventListener('avicore:pwa-install-ready', () => this.onInstallReady());
                window.addEventListener('avicore:pwa-installed', () => this.onInstalled());

                if (install.hasPrompt()) {
                    this.onInstallReady();
                } else if (this.isIos) {
                    this.scheduleShow();
                }
            },
            onInstallReady() {
                this.canInstall = true;
                this.scheduleShow();
            },
            onInstalled() {
                this.pwa()?.clearSessionDismiss();
                this.visible = false;
                this.canInstall = false;
            },
            scheduleShow() {
                clearTimeout(this.showTimer);
                this.showTimer = setTimeout(() => {
                    if (this.pwa()?.shouldShowBanner()) {
                        this.visible = true;
                    }
                }, this.delayMs);
            },
            dismiss() {
                this.pwa()?.dismissThisSession();
                this.visible = false;
            },
            async install() {
                const choice = await this.pwa()?.prompt();

                if (! choice) {
                    return;
                }

                this.visible = false;

                if (choice.outcome === 'dismissed') {
                    this.pwa()?.dismissThisSession();
                }
            },
        }"
        x-show="visible"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        role="region"
        aria-label="Instalar AviCore"
    >
        <div class="avicore-pwa-install__card">
            <img
                src="{{ asset('images/brand/pwa-192.png') }}"
                alt=""
                class="avicore-pwa-install__icon"
                width="40"
                height="40"
            >
            <div class="avicore-pwa-install__body">
                <p class="avicore-pwa-install__title">Instalá AviCore en tu celular</p>
                <p class="avicore-pwa-install__text" x-show="canInstall" x-cloak>
                    Accedé más rápido al galpón, como una app — sin pasar por la tienda.
                </p>
                <p class="avicore-pwa-install__text" x-show="isIos && ! canInstall" x-cloak>
                    En Safari: Compartir → Añadir a pantalla de inicio.
                </p>
            </div>
            <div class="avicore-pwa-install__actions">
                <button
                    type="button"
                    class="avicore-pwa-install__btn avicore-pwa-install__btn--primary"
                    x-show="canInstall"
                    x-cloak
                    x-on:click="install()"
                >
                    Instalar app
                </button>
                <button
                    type="button"
                    class="avicore-pwa-install__btn avicore-pwa-install__btn--ghost"
                    x-on:click="dismiss()"
                >
                    Ahora no
                </button>
            </div>
        </div>
    </div>
@endif
