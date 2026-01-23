import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'dark-bg': '#0F0F13',
                'dark-surface': '#1D1D26',
                // Accents extracted from image
                'accent-green': '#4ADE80',
                'accent-red': '#F87171',
                'accent-orange': '#FB923C',
                'accent-purple': '#818CF8',
                'accent-yellow': '#FACC15',
            },
            boxShadow: {
                'glow': '0 0 10px rgba(74, 222, 128, 0.5)',
                'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
            }
        },
    },

    plugins: [forms],
};
