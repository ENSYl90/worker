<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('dynamic_link.opening_app', ['app' => $appName]) }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h2>{{ __('dynamic_link.opening_app', ['app' => $appName]) }}</h2>
        <p>{{ __('dynamic_link.auto_redirect_message') }}</p>
        <a href="{{ $fallbackUrl }}" class="download-btn">{{ __('dynamic_link.download_from_app_store') }}</a>
    </div>

    <script>
        window.location.href = '{{ $universalLink }}';

        setTimeout(function() {
            window.location.href = '{{ $fallbackUrl }}';
        }, 3000);
    </script>
</body>
</html>