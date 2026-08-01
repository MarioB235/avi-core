@if (config('avicore.pwa.install_prompt'))
    <div
        class="avicore-pwa-install"
        x-data="{
            visible: false,
            isIos: false,
            deferredPrompt: null,
            storageKey: 'avicore-pwa-install-dismissed',
            init() {
                if (this.isInstalled() || this.wasDismissed()) {
                    return;
                }

                this.isIos = this.detectIos();

                window.addEventListener('beforeinstallprompt', (event) => {
                    event.preventDefault();
                    this.deferredPrompt = event;
                    this.visible = true;
                });

                if (this.isIos) {
                    this.visible = true;
                }
            },
            isInstalled() {
                return window.matchMedia('(display-mode: standalone)').matches
                    || window.navigator.standalone === true;
            },
            wasDismissed() {
                try {
                    return localStorage.getItem(this.storageKey) === '1';
                } catch {
                    return false;
                }
            },
            dismiss() {
                try {
                    localStorage.setItem(this.storageKey, '1');
                } catch {
                    // Sin localStorage (modo privado): solo ocultar en esta visita.
                }

                this.visible = false;
            },
            async install() {
                if (! this.deferredPrompt) {
                    return;
                }

                this.deferredPrompt.prompt();
                await this.deferredPrompt.userChoice;
                this.deferredPrompt = null;
                this.visible = false;
            },
            detectIos() {
                return /iphone|ipad|ipod/i.test(window.navigator.userAgent)
                    || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);
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
                <p class="avicore-pwa-install__title">Instalá AviCore</p>
                <p class="avicore-pwa-install__text" x-show="! isIos">
                    Accedé más rápido desde tu celular, como una app.
                </p>
                <p class="avicore-pwa-install__text" x-show="isIos" x-cloak>
                    En Safari: Compartir → Añadir a pantalla de inicio.
                </p>
            </div>
            <div class="avicore-pwa-install__actions">
                <button
                    type="button"
                    class="avicore-pwa-install__btn avicore-pwa-install__btn--primary"
                    x-show="! isIos && deferredPrompt"
                    x-on:click="install()"
                >
                    Instalar
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
