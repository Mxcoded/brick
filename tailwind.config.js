import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.{blade.php,js,ts,jsx,tsx}',
        './resources/**/*.vue',
        './Modules/**/*.blade.php',
        './Modules/**/*.js',
    ],
     theme: {
    extend: {
      colors: {
        primary: {
          50: '#fdf8f6',
          100: '#f2e8e5',
          200: '#eaddd7',
          300: '#e0cec7',
          400: '#d2bab0',
          500: '#bfa094',
          600: '#a18072',
          700: '#977669',
          800: '#846358',
          900: '#43302b',
          950: '#1c1714',
        },
        accent: {
          50: '#f6f7f6',
          100: '#e3e6e3',
          200: '#c8cdc8',
          300: '#a4afa4',
          400: '#7a8c7a',
          500: '#5e6e5e',
          600: '#4a584a',
          700: '#3d483d',
          800: '#333b33',
          900: '#2b312b',
          950: '#141814',
        },
        warm: {
          50: '#fefcfa',
          100: '#fdf6f0',
          200: '#faebdb',
          300: '#f5d9be',
          400: '#edbf8f',
          500: '#e5a263',
          600: '#d48542',
          700: '#b06a34',
          800: '#8e552e',
          900: '#734729',
          950: '#3e2513',
        },
      },
      fontFamily: {
        serif: ['Playfair Display', 'Georgia', 'serif'],
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
    },
  },

    plugins: [forms],
};
