/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
window.$ = window.jQuery = require('jquery');

window.Translator = require('bazinga-translator');
window.Routing = require('../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js');
const routes = require('./../../public/js/fos_js_routes.json');

Routing.setRoutingData(routes);

// Popper JS
require('popper.js');

// Bootstrap Scripts
require('bootstrap');

// Template Scripts
require('startbootstrap-sb-admin/js/sb-admin.min');
