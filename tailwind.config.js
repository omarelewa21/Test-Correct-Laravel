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
                midgrey: '#929DAF',
            },
        },
    },
    variants: {
        extend: {},
    },
    plugins: [],
}
