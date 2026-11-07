import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                asbank: {
                    dark: '#080E1A',
                    darker: '#050912',
                    card: '#0D1728',
                    cardBorder: '#1A2942',
                    sidebar: '#0A1324',
                    blue: {
                        light: '#38BDF8',
                        DEFAULT: '#0284C7',
                        hover: '#0369A1',
                        dark: '#075985',
                    },
                    gold: {
                        light: '#FDE047',
                        DEFAULT: '#D4AF37',
                        hover: '#C29B27',
                        dark: '#A17D16',
                        muted: '#E5C07B',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
