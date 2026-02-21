import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],

    // Développement local : Laravel (ex. localhost:8000) charge le JS depuis ce serveur
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true, // échoue si le port est déjà pris au lieu d’en prendre un autre
        hmr: {
            host: 'localhost',
            port: 5173,
        },
    },

    build: {
        outDir: 'public/build',
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
            },
        },
    },

    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
});
