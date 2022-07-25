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
const autoprefixer = require('autoprefixer');

mix.postCss("resources/css/app.css", "public/css", [
    require("tailwindcss"),
]).postCss("resources/css/app_pdf.css", "public/css/", [
    require("tailwindcss"), autoprefixer({overrideBrowserslist: [
            "> 1%",
            "last 20 versions"
        ]})
]).js('resources/js/app.js', 'public/js');

const wirisPath = "node_modules/@wiris/mathtype-ckeditor4";
mix.copy(wirisPath + "/plugin.js", "public/ckeditor/plugins/ckeditor_wiris/plugin.js")
    .copyDirectory(wirisPath + "/icons", "public/ckeditor/plugins/ckeditor_wiris/icons");

mix.copy("resources/ckeditor5/build/ckeditor.js", "public/js/ckeditor.js");
mix.copy("resources/js/readspeaker_tlc.js", "public/js/readspeaker_tlc.js");
mix.copy("resources/css/rs_tlc.css", "public/css/rs_tlc.css");
mix.copy("resources/css/rs_tlc_pdf.css", "public/css/rs_tlc_pdf.css");

if (mix.inProduction()) {
    mix.version();
}