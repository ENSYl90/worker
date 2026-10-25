@props([
    'callback' => 'initMap',
    'libraries' => null,
])

<script
    src="https://maps.googleapis.com/maps/api/js?key={{ setting('googleMapWebKey', '') }}&callback={{ $callback }}{{ $libraries ? '&libraries=' . $libraries : '' }}&v=weekly&loading=async"
    async></script>
