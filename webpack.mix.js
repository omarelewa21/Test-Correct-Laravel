const mix = require('laravel-mix');
const fs = require('fs');
const { execSync } = require('child_process');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// mix.js('resources/js/app.js', 'public/js')
//     .sass('resources/sass/app.scss', 'public/css');

mix.postCss("resources/css/app.css", "public/css", [
    require("tailwindcss"),
]).js('resources/js/app.js', 'public/js');

const wirisPath = "node_modules/@wiris/mathtype-ckeditor4";
mix.copy(wirisPath + "/plugin.js", "public/ckeditor/plugins/ckeditor_wiris/plugin.js")
    .copyDirectory(wirisPath + "/icons", "public/ckeditor/plugins/ckeditor_wiris/icons");

mix.copy("resources/ckeditor5/build/ckeditor.js", "public/js/ckeditor.js");

if (mix.inProduction()) {
    mix.version();
}