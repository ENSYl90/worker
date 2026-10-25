<!DOCTYPE html>
<html lang="{{ setting('localeCode', 'en') }}" dir="{{ isRTL() ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ setting('favicon') }}" />
    <title>@yield('title', '') - {{ setting('websiteName', env('APP_NAME')) }}</title>
    @include('layouts.partials.styles')
    @yield('styles')
    @stack('styles')
</head>

<body>
    {{-- flash message --}}
    <x-flash-messages />
    {{ $slot ?? '' }}
    @yield('content')

    {{-- footer --}}
    @include('layouts.partials.scripts')
    @stack('scripts')
</body>

</html>