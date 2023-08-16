const Encore = require('@symfony/webpack-encore');
const path = require("path");

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .addStyleEntry('bootstrap', './node_modules/bootstrap/dist/css/bootstrap.css')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableSingleRuntimeChunk()
    .enableSassLoader()
    .addAliases({
        '@symfony/stimulus-bridge/controllers.json': path.resolve(__dirname, 'node_modules', '@symfony', 'stimulus-bridge', 'dist', 'controllers.json'),
    });

module.exports = Encore.getWebpackConfig();
