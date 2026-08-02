import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Inter', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: [
                'images/brand/pwa-192.png',
                'images/brand/pwa-512.png',
                'images/brand/pwa-512-maskable.png',
                'images/brand/admin-home-hero.jpg',
                'images/brand/login-background.jpg',
            ],
            manifest: {
                id: '/',
                name: 'AviCore',
                short_name: 'AviCore',
                description: 'Gestión operativa avícola',
                theme_color: '#1f5e3b',
                background_color: '#f5f7f4',
                display: 'standalone',
                scope: '/',
                start_url: '/',
                lang: 'es',
                categories: ['business', 'productivity'],
                icons: [
                    {
                        src: '/images/brand/pwa-192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/images/brand/pwa-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/images/brand/pwa-512-maskable.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable',
                    },
                ],
                screenshots: [
                    {
                        src: '/images/brand/login-background.jpg',
                        sizes: '1080x1920',
                        type: 'image/jpeg',
                        form_factor: 'narrow',
                        label: 'Inicio de sesión AviCore',
                    },
                    {
                        src: '/images/brand/admin-home-hero.jpg',
                        sizes: '1920x1080',
                        type: 'image/jpeg',
                        form_factor: 'wide',
                        label: 'Panel administrativo AviCore',
                    },
                ],
                shortcuts: [
                    {
                        name: 'Operario',
                        short_name: 'Operario',
                        description: 'Carga en campo',
                        url: '/operario',
                        icons: [
                            {
                                src: '/images/brand/pwa-192.png',
                                sizes: '192x192',
                                type: 'image/png',
                            },
                        ],
                    },
                    {
                        name: 'Administración',
                        short_name: 'Admin',
                        description: 'Panel de administración',
                        url: '/admin',
                        icons: [
                            {
                                src: '/images/brand/pwa-192.png',
                                sizes: '192x192',
                                type: 'image/png',
                            },
                        ],
                    },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,woff2}'],
                navigateFallback: null,
                cleanupOutdatedCaches: true,
            },
            devOptions: {
                enabled: true,
            },
        }),
    ],
    server: {
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
