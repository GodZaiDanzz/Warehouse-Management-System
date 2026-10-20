import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/apexcharts')) {
                        return 'apexcharts';
                    }
                    if (id.includes('node_modules/html5-qrcode')) {
                        return 'html5-qrcode';
                    }
                    if (id.includes('node_modules/qrcode')) {
                        return 'qrcode';
                    }
                    if (id.includes('node_modules/sweetalert2')) {
                        return 'sweetalert2';
                    }
                }
            }
        },
        chunkSizeWarningLimit: 1000,
    },
});