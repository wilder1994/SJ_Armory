import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';

const __dirname = dirname(fileURLToPath(import.meta.url));

/**
 * Build local: public/build. Variables desde .env + .env.local (+ opcional .env.localbuild).
 * npm run build usa --mode localbuild para no leer .env.production de la raíz (eso es solo build_hosting).
 */
export default defineConfig({
    envDir: resolve(__dirname),
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/map.js',
                'resources/js/location-picker.js',
                'resources/js/weapon-photo-editor.js',
            ],
            refresh: true,
            publicDirectory: 'public',
            hotFile: 'public/hot',
        }),
    ],
});
