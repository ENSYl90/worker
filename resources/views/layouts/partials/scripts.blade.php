<livewire:scripts />
<x-livewire-alert::scripts />
{{-- <script src="{{ asset('js/app.js') }}"></script> --}}
<script src="{{ mix('js/app.js') }}"></script>

<!-- Lazy Load Utility -->
<script src="{{ asset('js/charts.js') }}"></script>
<script src="{{ asset('js/init-alpine.js') }}"></script>
<script src="{{ asset('js/main.js') }}"></script>
<script src="{{ asset('js/loading-overlay.js') }}"></script>

@include('layouts.partials.notification')

<x-backend-notifications />