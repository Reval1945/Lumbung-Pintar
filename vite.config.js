import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',

                // Public / User
                'resources/js/user/home.js',
                'resources/js/user/news.js',
                'resources/js/user/detail.js',

                // Admin
                'resources/js/admin/dashboard.js',
                'resources/js/admin/news.js',
                'resources/js/admin/news-create.js',
                'resources/js/admin/news-edit.js',
                'resources/js/admin/categories.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});