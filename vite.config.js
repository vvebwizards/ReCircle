import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/style.css',
                'resources/css/update-material.css',
                'resources/css/material-details.css',
                'resources/css/products.css',
                'resources/css/create-product.css',
                'resources/css/product_details.css',
                'resources/css/edit-product.css',
                'resources/css/maker_stats.css',
                'resources/css/buyerMarketplace.css',
                'resources/css/dashboard.css',
                'resources/js/app.js',
                'resources/js/main.js',
                'resources/js/auth.js',
                'resources/js/dashboard.js',
                'resources/js/twofa.js',
                'resources/js/twofactor-settings.js',
                'resources/js/forgot-password.js',
                'resources/js/material-create.js',
                'resources/js/update-material.js',
                'resources/js/bidSocket.js',
                'resources/js/marketplace.js',
                'resources/js/dashboardBids.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
