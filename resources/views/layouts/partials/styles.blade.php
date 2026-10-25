<!-- Tailwind -->
{{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
<link href="{{ mix('css/app.css') }}" rel="stylesheet">
<livewire:styles />
<link href="{{ asset('css/main.css') }}" rel="stylesheet">

{{-- <!-- FontAwesome Local -->
<link rel="stylesheet" href="{{ asset('vendor/fontawesome/all.min.css') }}" /> --}}
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@php
    $colors = getAppColorShades();
@endphp

<style>
    .focus\:shadow-outline-primary:focus {
        box-shadow: 0 0 0 3px
            {{ $colors['hsla'] }}
        ;
    }

    @foreach ($colors['shades'] as $shade => $value)
        .bg-primary-{{ $shade }} {
            background-color:
                {{ $value }}
                !important;
        }

        .hover\:bg-primary-{{ $shade }}:hover {
            background-color:
                {{ $value }}
                !important;
        }

        .text-primary-{{ $shade }},
        .ring-primary-{{ $shade }},
        .border-primary-{{ $shade }},
        .focus\:border-primary-{{ $shade }}:focus,
        .border-primary-{{ $shade }}:focus {
            color:
                {{ $value }}
                !important;
            border-color:
                {{ $value }}
                !important;
        }

    @endforeach .text-theme {
        color:
            {{ $colors['isDark'] ? '#fff' : '#000' }}
            !important;
    }

    .bg-theme {
        background-color:
            {{ $colors['isDark'] ? '#fff' : '#000' }}
            !important;
    }

    .text-xxs {
        font-size: 10px;
    }
</style>