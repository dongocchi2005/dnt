import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', ...defaultTheme.fontFamily.sans],
                display: ['DM Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                bg0: 'rgb(var(--c-bg-0) / <alpha-value>)',
                bg1: 'rgb(var(--c-bg-1) / <alpha-value>)',
                ink: {
                    DEFAULT: 'rgb(var(--c-text) / <alpha-value>)',
                    muted: 'rgb(var(--c-muted) / <alpha-value>)',
                },
                neon: {
                    cyan: 'rgb(var(--c-cyan) / <alpha-value>)',
                    blue: 'rgb(var(--c-blue) / <alpha-value>)',
                    purple: 'rgb(var(--c-purple) / <alpha-value>)',
                    gold: 'rgb(var(--c-gold) / <alpha-value>)',
                },
            },
            boxShadow: {
                'glass': '0 10px 40px rgba(0,0,0,.45)',
                'neon-cyan': '0 0 0 1px rgba(34,211,238,.25), 0 0 24px rgba(34,211,238,.35)',
                'neon-purple': '0 0 0 1px rgba(168,85,247,.22), 0 0 28px rgba(168,85,247,.30)',
            },
            backdropBlur: {
                xs: '2px',
            },
        },
    },

    plugins: [forms],
};
