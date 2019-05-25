const Encore = require('@symfony/webpack-encore');

Encore
// directory where compiled assets will be stored
  .setOutputPath('public/build/')
// public path used by the web server to access the output path
  .setPublicPath('/build')
// only needed for CDN's or sub-directory deploy
// .setManifestKeyPrefix('build/')

/*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
  .addEntry('app', './assets/js/app.js')
  .addEntry('product', './assets/js/Products/index.js')
  .addEntry('bar-code', './assets/js/Products/BarCode.js')
  .addEntry('incoming', './assets/js/Products/IncomingProducts.js')
  .addEntry('order', './assets/js/Orders/index.js')
  .addEntry('order/new', './assets/js/Orders/new.js')
  .addEntry('order/getting-ready', './assets/js/Orders/gettingReady.js')
  .addEntry('customer', './assets/js/Customer/Index.js')
  .addEntry('customer/edit', './assets/js/Customer/Edit.js')
  .addEntry('customer/new', './assets/js/Customer/New.js')

/*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
// enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

// enables Sass/SCSS support
// .enableSassLoader()

// uncomment if you use TypeScript
// .enableTypeScriptLoader()

// uncomment if you're having problems with a jQuery plugin
// .autoProvidejQuery()

  .enableReactPreset();

module.exports = Encore.getWebpackConfig();
