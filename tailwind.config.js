module.exports = {
    mode: 'jit',
    purge: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: false, // or 'media' or 'class'
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
            },
            borderWidth: {
                '3': '3px'
            }
        },
    },
    variants: {
        extend: {},
    },
    plugins: [],
}
