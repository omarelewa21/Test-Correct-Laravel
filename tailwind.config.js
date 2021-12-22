module.exports = {
    mode: 'jit',
    purge: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    darkMode: false, // or 'media' or 'class'
    theme: {
        extend: {},
        colors: {
            white: '#ffffff',
            primary: '#004df5',
            base: '#041f74',
        },
    },
    variants: {
        extend: {},
    },
    plugins: [],
}
