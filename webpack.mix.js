const mix = require('laravel-mix');
const autoprefixer = require('autoprefixer');

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

// const SpeedMeasurePlugin = require("speed-measure-webpack-plugin");
// const smp = new SpeedMeasurePlugin();
// const webpack = smp.wrap({});

const webpack = {};
const prefixerPlugin = autoprefixer({
    overrideBrowserslist: [
        "chrome 6", "safari 5.1"
    ]
});
const mixOptions = {
    postCss: [require('tailwindcss')]
}


mix.webpackConfig(webpack);
mix.options(mixOptions);

mix.postCss("resources/css/app.css", "public/css")
    .js('resources/js/app.js', 'public/js');

// if (mix.inProduction()) {
    mix.postCss("resources/css/app_pdf.css", "public/css/")
        .postCss("resources/css/print-test-pdf.css", "public/css/", [prefixerPlugin])
// }

const wirisPath = "node_modules/@wiris/mathtype-ckeditor4";
mix.copy(wirisPath + "/plugin.js", "public/ckeditor/plugins/ckeditor_wiris/plugin.js")
    .copyDirectory(wirisPath + "/icons", "public/ckeditor/plugins/ckeditor_wiris/icons");

mix.copy("resources/ckeditor5/build/ckeditor.js", "public/js/ckeditor.js");
mix.copy("resources/ckeditor5/build/ckeditor_teacher.js", "public/js/ckeditor_teacher.js");
mix.copy("resources/ckeditor5/build/ckeditor_teacher_wsc.js", "public/js/ckeditor_teacher_wsc.js");
mix.copy("resources/js/readspeaker_tlc.js", "public/js/readspeaker_tlc.js");
mix.copy("resources/css/rs_tlc.css", "public/css/rs_tlc.css");
mix.copy("resources/css/rs_tlc_pdf.css", "public/css/rs_tlc_pdf.css");

if (mix.inProduction()) {
    mix.version();
}