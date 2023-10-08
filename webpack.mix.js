const mix = require('laravel-mix');

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

mix.js('resources/js/app.js', 'public/js')
    .extract(['vue'])
    .copy('resources/sass/bootstrap.min.css', 'public/css')
    .copy('resources/sass/print.kidlat.css', 'public/css')
    .copy('resources/sass/print.css', 'public/css')
    .sass('resources/sass/app.scss', 'public/css')
    .options({processCssUrls: false})
    .version();

/**
 * Webpack configuration
 */

mix.webpackConfig({
    plugins: [
    ],
    resolve: {
      alias: {
        "requestfactory": path.resolve(__dirname, './resources/js/request/RequestFactory.js'),
        "common": path.resolve(__dirname, './resources/js/common'),
        "components": path.resolve(__dirname, './resources/js/pages/common'),
        "pages": path.resolve(__dirname, './resources/js/pages'),
      }
    },
});
