const mix = require("laravel-mix");
const fs = require("fs");
const { execSync } = require("child_process");

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

/* Whenever a --stats-children warning occurs, enable this to track down the issue in the file (usually CSS) */
// mix.webpackConfig({
//     stats: {
//         children: true,
//     },
// });
const autoprefixer = require("autoprefixer");

mix.postCss("resources/css/app.css", "public/css/", [
    require("tailwindcss")
]).js("resources/js/app.js", "public/js/");

if (typeof process.env.MIX_DEVELOPMENT_BUILD !== "undefined" && process.env.MIX_DEVELOPMENT_BUILD === "true") {
    console.info("Asset building in development mode.");
}

if (typeof process.env.MIX_DEVELOPMENT_BUILD === "undefined" || process.env.MIX_DEVELOPMENT_BUILD === "false") {
    console.info("Asset building in production mode.");
    mix.postCss("resources/css/app_pdf.css", "public/css/", [
        require("tailwindcss")
    ]).postCss("resources/css/print-test-pdf.css", "public/css/", [
        autoprefixer({
            overrideBrowserslist: [
                "chrome 6", "safari 5.1"
            ]
        })
    ]);
}


mix.copy("resources/ckeditor5/build/ckeditor.js", "public/js/ckeditor.js");
mix.copy("resources/ckeditor5/build/ckeditor.js.map", "public/js/ckeditor.js.map");
mix.copy("resources/js/readspeaker_tlc.js", "public/js/readspeaker_tlc.js");
mix.copy("resources/css/rs_tlc.css", "public/css/rs_tlc.css");
mix.copy("resources/css/rs_tlc_pdf.css", "public/css/rs_tlc_pdf.css");

if (mix.inProduction()) {
    mix.version();
}
