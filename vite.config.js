import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
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
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
            '@images': resolve(__dirname, 'resources/images'),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 5 uses legacy @import and old Sass built-ins.
                // quietDeps silences warnings from node_modules (Bootstrap internals).
                // silenceDeprecations covers our own @import usage until Bootstrap 6
                // ships with full @use/@forward support.
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/vue') || id.includes('node_modules/@vue')) {
                        return 'vendor-vue';
                    }
                    if (id.includes('node_modules/@inertiajs')) {
                        return 'vendor-inertia';
                    }
                    if (id.includes('node_modules/bootstrap')) {
                        return 'vendor-bootstrap';
                    }
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
