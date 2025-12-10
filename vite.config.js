import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // Общие модули в отдельный chunk
                    if (id.includes('resources/js/modules/')) {
                        return 'vendor-modules';
                    }
                    // Страничные модули будут автоматически разбиты на отдельные chunks через динамические импорты
                },
            },
        },
        chunkSizeWarningLimit: 1000,
    },
    server: {
        cors: true,
    },
});