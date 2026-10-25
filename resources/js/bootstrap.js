window._ = require('lodash');

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo/Pusher are intentionally NOT bundled here. They are loaded via CDN
 * <script> tags in resources/views/layouts/partials/scripts.blade.php so
 * that websocket connection details can change without a Mix rebuild.
 * See public/js/backend-notifications.js for the Echo instantiation.
 */
window.io = require('socket.io-client');