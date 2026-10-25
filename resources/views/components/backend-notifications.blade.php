@auth
    @if (canI('recieve-system-notifications'))
        <div id="reverb-config" data-key="{{ config('broadcasting.connections.reverb.key') }}"
            data-host="{{ config('broadcasting.connections.reverb.options.host', request()->getHost()) }}"
            data-port="{{ config('broadcasting.connections.reverb.options.port', 80) }}"
            data-scheme="{{ config('broadcasting.connections.reverb.options.scheme', 'http') }}"
            data-soundconfig="{{ url('api/setting/notification/sound-config') }}"
            data-csrf="{{ csrf_token() }}" style="display:none">
        </div>

        <div id="backend-notification-container"
            class="fixed z-50 space-y-2 transform -translate-x-1/2 top-4 left-1/2"
            style="max-width: 500px; width: 90%;"></div>

        <template id="backend-notification-toast-template">
            <div class="backend-notification-toast transform transition-all duration-300 ease-in-out opacity-0 translate-y-2
                px-6 py-4 rounded-lg shadow-lg border-l-4 flex items-center justify-between space-x-4">
                <div class="flex items-center space-x-3 flex-1">
                    <div class="flex-shrink-0 backend-notification-toast-icon"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium backend-notification-toast-message"></p>
                    </div>
                </div>
                <button type="button" class="flex-shrink-0 inline-flex text-gray-400 hover:text-gray-500
                    focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 rounded">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </template>

        <template id="backend-notification-icon-success">
            <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </template>

        <template id="backend-notification-icon-error">
            <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </template>

        <template id="backend-notification-icon-warning">
            <svg class="h-6 w-6 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </template>

        <template id="backend-notification-icon-info">
            <svg class="h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </template>

        <template id="backend-notification-icon-created">
            <svg class="h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </template>

        <template id="backend-notification-icon-updated">
            <svg class="h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </template>

        <template id="backend-notification-icon-deleted">
            <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </template>

        {{-- Pusher/Echo are vendored as static files (not Mix-bundled) so websocket
             connection details (host/port/scheme/key) can change without a rebuild,
             and notifications keep working offline / if a CDN is unreachable.
             Pinned versions: pusher-js@8.6.0, laravel-echo@2.4.0.
             Re-download from jsDelivr to upgrade:
             https://cdn.jsdelivr.net/npm/pusher-js@<version>/dist/web/pusher.min.js
             https://cdn.jsdelivr.net/npm/laravel-echo@<version>/dist/echo.iife.js --}}
        <script src="{{ asset('js/vendor/pusher.min.js') }}"></script>
        <script src="{{ asset('js/vendor/echo.iife.js') }}"></script>
        <script src="{{ asset('js/backend-notifications.js') }}"></script>
    @endif
@endauth
