import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/style.css',
                'resources/css/material-create.css',
                'resources/css/update-material.css',
                'resources/css/material-details.css',
                'resources/js/app.js',
                'resources/js/main.js',
                'resources/js/auth.js',
                'resources/js/dashboard.js',
                'resources/js/twofa.js',
                'resources/js/twofactor-settings.js',
                'resources/js/forgot-password.js',
                'resources/js/material-create.js',
                'resources/js/update-material.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
