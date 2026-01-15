import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js', 
                'resources/css/pages/booking.css', 
                'resources/js/pages/booking.js', 
                'resources/js/ui-cyber.js',
                'resources/css/pages/product-show.css',
                'resources/js/pages/product-show.js',
                'resources/css/pages/clearance-show.css',
                'resources/js/pages/clearance-show.js'
            ],
            refresh: true,
        }),
    ],
});
