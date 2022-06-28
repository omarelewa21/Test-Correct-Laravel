module.exports = {
    // mode: 'jit',
    // purge: {
    //     content: [
    //         './vendor/wire-elements/modal/resources/views/*.blade.php',
    //         './resources/**/*.blade.php',
    //         './resources/**/*.js',
    //         './resources/**/*.vue',
    //     ],
    //     safelist: [
    //         'sm:w-full',
    //         'sm:max-w-md',
    //         'md:max-w-xl',
    //         'lg:max-w-3xl',
    //         'xl:max-w-5xl',
    //         '2xl:max-w-6xl',
    //         'max-w-[600px]'
    //     ]
    // },
    content: [
        './vendor/wire-elements/modal/resources/views/*.blade.php',
        './resources/**/*.{blade.php,js}',
    ],
    safelist: [
        'sm:w-full',
        'sm:max-w-md',
        'md:max-w-xl',
        'lg:max-w-3xl',
        'xl:max-w-5xl',
        '2xl:max-w-6xl',
        'max-w-[600px]'
    ],
    theme: {
        extend: {
            colors: {
                primary: '#004df5',
                secondary: '#CEDAF3',
                sysbase: '#041f74',
                bluegrey: '#c3d0ed',
                offwhite: '#f9faff',
                allred: '#cf1b04',
                cta: '#3ab753',
                ctamiddark: '#27973D',
                ctadark: '#006314',
                midgrey: '#929DAF',
                student: '#ECDB00',
                lightgreen: '#95cd3e',
                orange: '#eca000',
                note: '#6b7789',
                lightGrey: '#F0F2F5',
            },
            borderWidth: {
                '3': '3px',
                '6': '6px',
            }
        },
        keyframes: {
            knightrider: {
                '0%': {left: '0'},
                '50%': {left: '85%'},
                '100%': {left: '0'}
            },
            borderPulse: {
                '0%': {'border-color': 'rgba(255,255,255, 1)'},
                '50%': {'border-color': 'rgba(255,255,255, .2)'},
                '100%': {'border-color': 'rgba(255,255,255, 1)'}
            }
        },
        animation: {
            'knightrider': 'knightrider 2s ease infinite',
            'borderpulse': 'borderPulse 3s ease infinite'
        }
    },
    plugins: [
        require('@tailwindcss/line-clamp')
    ],
}
