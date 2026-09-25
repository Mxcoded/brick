import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './Modules/**/*.blade.php',
        './Modules/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    50: 'oklch(var(--primary-50) / <alpha-value>)',
                    100: 'oklch(var(--primary-100) / <alpha-value>)',
                    200: 'oklch(var(--primary-200) / <alpha-value>)',
                    300: 'oklch(var(--primary-300) / <alpha-value>)',
                    400: 'oklch(var(--primary-400) / <alpha-value>)',
                    500: 'oklch(var(--primary-500) / <alpha-value>)',
                    600: 'oklch(var(--primary-600) / <alpha-value>)',
                    700: 'oklch(var(--primary-700) / <alpha-value>)',
                    800: 'oklch(var(--primary-800) / <alpha-value>)',
                    900: 'oklch(var(--primary-900) / <alpha-value>)',
                    950: 'oklch(var(--primary-950) / <alpha-value>)',
                },
                danger: { 50: 'oklch(var(--danger-50) / <alpha-value>)', 100: 'oklch(var(--danger-100) / <alpha-value>)', 200: 'oklch(var(--danger-200) / <alpha-value>)', 300: 'oklch(var(--danger-300) / <alpha-value>)', 400: 'oklch(var(--danger-400) / <alpha-value>)', 500: 'oklch(var(--danger-500) / <alpha-value>)', 600: 'oklch(var(--danger-600) / <alpha-value>)', 700: 'oklch(var(--danger-700) / <alpha-value>)', 800: 'oklch(var(--danger-800) / <alpha-value>)', 900: 'oklch(var(--danger-900) / <alpha-value>)', 950: 'oklch(var(--danger-950) / <alpha-value>)' },
                warning: { 50: 'oklch(var(--warning-50) / <alpha-value>)', 100: 'oklch(var(--warning-100) / <alpha-value>)', 200: 'oklch(var(--warning-200) / <alpha-value>)', 300: 'oklch(var(--warning-300) / <alpha-value>)', 400: 'oklch(var(--warning-400) / <alpha-value>)', 500: 'oklch(var(--warning-500) / <alpha-value>)', 600: 'oklch(var(--warning-600) / <alpha-value>)', 700: 'oklch(var(--warning-700) / <alpha-value>)', 800: 'oklch(var(--warning-800) / <alpha-value>)', 900: 'oklch(var(--warning-900) / <alpha-value>)', 950: 'oklch(var(--warning-950) / <alpha-value>)' },
                success: { 50: 'oklch(var(--success-50) / <alpha-value>)', 100: 'oklch(var(--success-100) / <alpha-value>)', 200: 'oklch(var(--success-200) / <alpha-value>)', 300: 'oklch(var(--success-300) / <alpha-value>)', 400: 'oklch(var(--success-400) / <alpha-value>)', 500: 'oklch(var(--success-500) / <alpha-value>)', 600: 'oklch(var(--success-600) / <alpha-value>)', 700: 'oklch(var(--success-700) / <alpha-value>)', 800: 'oklch(var(--success-800) / <alpha-value>)', 900: 'oklch(var(--success-900) / <alpha-value>)', 950: 'oklch(var(--success-950) / <alpha-value>)' },
                info: { 50: 'oklch(var(--info-50) / <alpha-value>)', 100: 'oklch(var(--info-100) / <alpha-value>)', 200: 'oklch(var(--info-200) / <alpha-value>)', 300: 'oklch(var(--info-300) / <alpha-value>)', 400: 'oklch(var(--info-400) / <alpha-value>)', 500: 'oklch(var(--info-500) / <alpha-value>)', 600: 'oklch(var(--info-600) / <alpha-value>)', 700: 'oklch(var(--info-700) / <alpha-value>)', 800: 'oklch(var(--info-800) / <alpha-value>)', 900: 'oklch(var(--info-900) / <alpha-value>)', 950: 'oklch(var(--info-950) / <alpha-value>)' },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
