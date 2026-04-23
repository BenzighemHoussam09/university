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
            colors: {
                'primary':                    '#00342b',
                'on-primary':                 '#ffffff',
                'primary-container':          '#004d40',
                'on-primary-container':       '#7ebdac',
                'primary-fixed':              '#afefdd',
                'primary-fixed-dim':          '#94d3c1',
                'on-primary-fixed':           '#00201a',
                'on-primary-fixed-variant':   '#065043',
                'inverse-primary':            '#94d3c1',
                'surface-tint':               '#29695b',

                'secondary':                  '#465f88',
                'on-secondary':               '#ffffff',
                'secondary-container':        '#b6d0ff',
                'on-secondary-container':     '#3f5881',
                'secondary-fixed':            '#d6e3ff',
                'secondary-fixed-dim':        '#aec7f7',
                'on-secondary-fixed':         '#001b3d',
                'on-secondary-fixed-variant': '#2e476f',

                'tertiary':                   '#4e2013',
                'on-tertiary':                '#ffffff',
                'tertiary-container':         '#693527',
                'on-tertiary-container':      '#e89f8c',
                'tertiary-fixed':             '#ffdbd1',
                'tertiary-fixed-dim':         '#ffb5a1',
                'on-tertiary-fixed':          '#370e04',
                'on-tertiary-fixed-variant':  '#6d382a',

                'error':                      '#ba1a1a',
                'on-error':                   '#ffffff',
                'error-container':            '#ffdad6',
                'on-error-container':         '#93000a',

                'surface':                    '#f8f9fa',
                'surface-dim':                '#d9dadb',
                'surface-bright':             '#f8f9fa',
                'surface-container-lowest':   '#ffffff',
                'surface-container-low':      '#f3f4f5',
                'surface-container':          '#edeeef',
                'surface-container-high':     '#e7e8e9',
                'surface-container-highest':  '#e1e3e4',
                'surface-variant':            '#e1e3e4',
                'on-surface':                 '#191c1d',
                'on-surface-variant':         '#3f4945',
                'inverse-surface':            '#2e3132',
                'inverse-on-surface':         '#f0f1f2',

                'outline':                    '#707975',
                'outline-variant':            '#bfc9c4',

                'background':                 '#f8f9fa',
                'on-background':              '#191c1d',
            },

            borderRadius: {
                DEFAULT: '0.25rem',
                lg:      '0.5rem',
                xl:      '0.75rem',
                '2xl':   '1.5rem',
                full:    '9999px',
            },

            fontFamily: {
                headline: ['Public Sans', 'Cairo', ...defaultTheme.fontFamily.sans],
                body:     ['Inter',       'Cairo', ...defaultTheme.fontFamily.sans],
                label:    ['Inter',       'Cairo', ...defaultTheme.fontFamily.sans],
                sans:     ['Cairo',       'Inter', ...defaultTheme.fontFamily.sans],
            },

            boxShadow: {
                'ambient': '0 20px 40px rgba(25, 28, 29, 0.06)',
                'glass':   '0 4px 24px rgba(25, 28, 29, 0.08)',
            },
        },
    },

    plugins: [forms],
};
