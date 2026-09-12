/** @type {import('tailwindcss').Config} */
const defaultTheme = require('tailwindcss/defaultTheme');
const plugin = require('tailwindcss/plugin');

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // Pure Black & White monochrome system.
                // Preserving existing utility names so all markup works seamlessly.
                brand: {
                    50: '#F5F5F5',
                    100: '#E5E5E5',
                    200: '#CCCCCC',
                    300: '#AAAAAA',
                    400: '#888888',
                    500: '#666666',
                    600: '#333333',
                    700: '#1F1F1F',
                    800: '#0A0A0A',
                    900: '#000000',
                },
                primary: {
                    50: '#F5F5F5',
                    100: '#E5E5E5',
                    200: '#CCCCCC',
                    300: '#AAAAAA',
                    400: '#888888',
                    500: '#666666',
                    600: '#333333',
                    700: '#1F1F1F',
                    800: '#0A0A0A',
                    900: '#000000',
                },
                surface: {
                    0: '#FFFFFF',
                    50: '#F5F5F5',
                    100: '#E5E5E5',
                    200: '#CCCCCC',
                    300: '#AAAAAA',
                    400: '#888888',
                    500: '#666666',
                    600: '#333333',
                    700: '#1F1F1F',
                    800: '#0A0A0A',
                    900: '#000000',
                },
                navy: {
                    50: '#F5F5F5',
                    100: '#E5E5E5',
                    200: '#CCCCCC',
                    300: '#AAAAAA',
                    400: '#888888',
                    500: '#666666',
                    600: '#333333',
                    700: '#1F1F1F',
                    800: '#0A0A0A',
                    900: '#000000',
                    950: '#000000',
                },
                cyber: {
                    100: '#E5E5E5',
                    200: '#CCCCCC',
                    300: '#AAAAAA',
                    400: '#888888',
                    500: '#666666',
                    600: '#333333',
                    700: '#1F1F1F',
                    800: '#0A0A0A',
                    900: '#000000',
                },
            },
            animation: {
                'fade-in': 'fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
                'slide-up': 'slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
                'slide-in-left': 'slideInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
                'counter': 'counter 2s cubic-bezier(0.16, 1, 0.3, 1)',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'float': 'float 3s ease-in-out infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(30px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideInLeft: {
                    '0%': { opacity: '0', transform: 'translateX(-30px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
            },
            spacing: {
                '18': '4.5rem',
                '22': '5.5rem',
                '26': '6.5rem',
                '30': '7.5rem',
            },
            maxWidth: {
                '8xl': '88rem',
                '9xl': '96rem',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        plugin(function({ addUtilities }) {
            addUtilities({
                '.glass': {
                    'background': 'rgba(255, 255, 255, 0.1)',
                    'backdrop-filter': 'blur(10px)',
                    'border': '1px solid rgba(255, 255, 255, 0.2)',
                },
                '.preserve-3d': {
                    'transform-style': 'preserve-3d',
                },
                '.backface-hidden': {
                    'backface-visibility': 'hidden',
                },
            });
        }),
    ],
}
