<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('dynamic_link.opening_app', ['app' => $appName]) }}</title>
    <style>
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid white;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .download-btn {
            display: inline-block;
            background: white;
            color: #4CAF50;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 1rem;
            transition: transform 0.2s;
        }
        .download-btn:hover {
            transform: translateY(-2px);
        }
        .debug {
            font-size: 0.8em;
            opacity: 0.7;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h2>{{ __('dynamic_link.opening_app', ['app' => $appName]) }}</h2>
        <p>{{ __('dynamic_link.auto_redirect_message') }}</p>
        <a href="{{ $fallbackUrl }}" class="download-btn">{{ __('dynamic_link.download_from_play_store') }}</a>

        @if(config('app.debug'))
        <div class="debug">
            <p>Scheme: {{ $scheme }}</p>
            <p>Package: {{ $package }}</p>
            <p>ID: {{ $id }}</p>
        </div>
        @endif
    </div>

    <script>
        const scheme = '{{ $scheme }}';
        const packageName = '{{ $package }}';
        const itemId = '{{ $id }}';
        const fallbackUrl = '{{ $fallbackUrl }}';

        // Multiple deep link attempts for better compatibility
        function tryOpenApp() {
            const linkType = '{{ request()->segment(2) }}'; // Gets 'product', 'service', or 'vendor' from URL

            // Method 1: Direct scheme URL
            const directLink = `${scheme}://${linkType}/${itemId}`;

            // Method 2: Intent URL with fallback
            const intentUrl = `intent://${linkType}/${itemId}#Intent;scheme=${scheme};package=${packageName};S.browser_fallback_url=${encodeURIComponent(fallbackUrl)};end`;

            // Method 3: Android App Link (if supported)
            const appLink = `android-app://${packageName}/${scheme}/${linkType}/${itemId}`;

            console.log('Trying deep links:', {
                direct: directLink,
                intent: intentUrl,
                appLink: appLink
            });

            // Try direct scheme first
            window.location.href = directLink;

            // Fallback to intent URL after short delay
            setTimeout(() => {
                window.location.href = intentUrl;
            }, 100);

            // If nothing works, redirect to Play Store after 3 seconds
            setTimeout(() => {
                if (document.hidden === false) {
                    window.location.href = fallbackUrl;
                }
            }, 3000);
        }

        // Start the process immediately
        tryOpenApp();

        // Handle page visibility to prevent Play Store redirect if app opened
        let appOpened = false;

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                appOpened = true;
            }
        });

        // Prevent Play Store redirect if app was opened
        window.addEventListener('beforeunload', () => {
            appOpened = true;
        });

        // Additional attempt when page loads
        window.addEventListener('load', () => {
            if (!appOpened) {
                setTimeout(tryOpenApp, 500);
            }
        });
    </script>
</body>
</html>