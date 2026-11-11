import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/beranda-022.css',
                'resources/js/beranda-022.js',
                'resources/css/autentikasi-111.css',
                'resources/js/autentikasi-111.js',
                'resources/css/kalkulator-111.css',
                'resources/js/kalkulator-111.js',
                'resources/css/aplikasi-111.css',
                'resources/css/dashboard-111.css',
                'resources/css/target-pengingat-022.css',
                'resources/js/target-pengingat-022.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
