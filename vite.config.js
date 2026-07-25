import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/welcome.css', 'resources/css/auth/login.css', 'resources/css/auth/register.css', 'resources/css/auth/lupapassword.css', 'resources/css/pages/dashboard.css', 'resources/css/pages/kasir.css', 'resources/css/pages/kelolauser.css', 'resources/css/pages/laporan.css', 'resources/css/pages/manajemenbarang.css', 'resources/css/pages/profile.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
