/**
 * Backend Real-time Notifications via WebSocket
 * This script connects to Laravel Reverb WebSocket server and listens for model events
 * Permission-based notification system for backend users
 *
 * Markup (container, toast template, icon templates) lives in
 * resources/views/components/backend-notifications.blade.php.
 */

(function () {
    'use strict';

    const TOAST_DURATION = 5000; // Duration to show toast in milliseconds

    const TOAST_CLASSES = {
        'success': 'bg-green-50 border-green-500 text-green-800',
        'error': 'bg-red-50 border-red-500 text-red-800',
        'warning': 'bg-yellow-50 border-yellow-500 text-yellow-800',
        'info': 'bg-blue-50 border-blue-500 text-blue-800',
        'created': 'bg-green-50 border-green-500 text-green-800',
        'updated': 'bg-blue-50 border-blue-500 text-blue-800',
        'deleted': 'bg-red-50 border-red-500 text-red-800',
    };

    /**
     * Show toast notification by cloning the templates rendered by the
     * backend-notifications Blade component.
     */
    function showToast(message, type = 'info') {
        const container = document.getElementById('backend-notification-container');
        const toastTemplate = document.getElementById('backend-notification-toast-template');
        const iconTemplate = document.getElementById(`backend-notification-icon-${type}`) ||
            document.getElementById('backend-notification-icon-info');

        if (!container || !toastTemplate) {
            console.warn('Backend notifications: toast templates not found in DOM.');
            return;
        }

        const toast = toastTemplate.content.firstElementChild.cloneNode(true);
        toast.classList.add(...(TOAST_CLASSES[type] || TOAST_CLASSES['info']).split(' '));
        toast.querySelector('.backend-notification-toast-message').textContent = message;
        if (iconTemplate) {
            toast.querySelector('.backend-notification-toast-icon').appendChild(
                iconTemplate.content.cloneNode(true)
            );
        }

        toast.querySelector('button').addEventListener('click', () => removeToast(toast));

        container.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');
        }, 10);

        // Auto remove after duration
        setTimeout(() => removeToast(toast), TOAST_DURATION);
    }

    /**
     * Remove toast with animation
     */
    function removeToast(toast) {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'translate-y-2');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    /**
     * Format notification message
     */
    function formatNotificationMessage(data) {
        const { modelType, action, modelData } = data;
        const modelName = modelData.data.name || modelData.data.code || modelData.data.email || `#${modelData.id}`;

        const actionText = {
            'created': 'created',
            'updated': 'updated',
            'deleted': 'deleted'
        };

        return `${modelType} "${modelName}" has been ${actionText[action]}`;
    }

    /**
     * Initialize Echo from #reverb-config data attributes injected by Blade
     */
    function initEcho() {
        const el = document.getElementById('reverb-config');
        if (!el) {
            console.warn('Backend notifications: #reverb-config element not found.');
            return;
        }

        if (typeof window.Pusher === 'undefined') {
            console.warn('Backend notifications: Pusher not available on window.');
            return;
        }

        if (typeof window.Echo === 'undefined') {
            console.warn('Backend notifications: Echo class not available on window.');
            return;
        }

        // The Echo CDN (IIFE) build exposes a module namespace object whose
        // actual constructor lives at `.default`, rather than window.Echo
        // itself being the class. Support both shapes defensively.
        const EchoClass = typeof window.Echo === 'function' ? window.Echo : window.Echo.default;
        if (typeof EchoClass !== 'function') {
            console.warn('Backend notifications: Echo constructor not found on window.Echo.');
            return;
        }
        const port = parseInt(el.dataset.port, 10) || 80;
        const scheme = el.dataset.scheme || 'http';
        const forceTLS = scheme === 'https' || port === 443;

        window.Echo = new EchoClass({
            broadcaster: 'reverb',
            key: el.dataset.key,
            wsHost: el.dataset.host || window.location.hostname,
            wsPort: port,
            wssPort: port,
            forceTLS: forceTLS,
            enabledTransports: forceTLS ? ['ws', 'wss'] : ['ws'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': el.dataset.csrf,
                },
            },
        });

        console.log('Echo initialized from #reverb-config:', { scheme, port, forceTLS });
    }

    /**
     * Initialize WebSocket connection
     */
    function initWebSocket() {
        initEcho();

        if (typeof window.Echo === 'undefined') {
            console.warn('Laravel Echo is not loaded. WebSocket notifications will not work.');
            return;
        }

        console.log('Connecting to backend notifications channel...');

        // Subscribe to the private backend notifications channel
        window.Echo.private('backend-notifications')
            .listen('.model.notification', (data) => {
                console.log('Received notification:', data);

                // Format and show the notification
                const message = formatNotificationMessage(data);
                showToast(message, data.action);

                // Play notification sound (optional)
                playNotificationSound();
            })
            .error((error) => {
                console.error('WebSocket error:', error);
            });

        console.log('Successfully subscribed to backend notifications channel');
    }

    /**
     * Sound settings (loaded from database)
     */
    let soundSettings = {
        sound_enabled: true,
        sound_file: 'notification-1.mp3',
        sound_volume: 30
    };

    /**
     * Load sound settings from server
     */
    async function loadSoundSettings() {
        const el = document.getElementById('reverb-config');
        const soundConfigUrl = el && el.dataset.soundconfig;
        if (!soundConfigUrl) {
            console.warn('Backend notifications: data-soundconfig URL not found on #reverb-config.');
            return;
        }

        try {
            const response = await fetch(soundConfigUrl);
            if (response.ok) {
                soundSettings = await response.json();
                console.log('Sound settings loaded:', soundSettings);
            }
        } catch (error) {
            console.log('Could not load sound settings, using defaults:', error);
        }
    }

    /**
     * Play notification sound
     */
    function playNotificationSound() {
        // Check if sound is enabled
        if (!soundSettings.sound_enabled || soundSettings.sound_file === 'none') {
            return;
        }

        try {
            const audio = new Audio('/sounds/' + soundSettings.sound_file);
            audio.volume = soundSettings.sound_volume / 100;
            audio.play().catch(err => {
                // User interaction required to play audio
                console.log('Audio play prevented:', err);
            });
        } catch (error) {
            console.log('Notification sound not available:', error);
        }
    }

    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            loadSoundSettings();
            initWebSocket();
        });
    } else {
        loadSoundSettings();
        initWebSocket();
    }

    // Export for testing/debugging
    window.BackendNotifications = {
        showToast,
        initWebSocket,
        loadSoundSettings
    };

})();
