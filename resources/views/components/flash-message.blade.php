@props(['type' => 'info', 'title' => null, 'message' => null, 'dismissible' => true, 'duration' => 5000])

@php
    $types = [
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'text' => 'text-green-800',
            'icon_bg' => 'bg-green-100',
            'icon_color' => 'text-green-600',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'text' => 'text-red-800',
            'icon_bg' => 'bg-red-100',
            'icon_color' => 'text-red-600',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
        ],
        'warning' => [
            'bg' => 'bg-yellow-50',
            'border' => 'border-yellow-200',
            'text' => 'text-yellow-800',
            'icon_bg' => 'bg-yellow-100',
            'icon_color' => 'text-yellow-600',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />'
        ],
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-blue-800',
            'icon_bg' => 'bg-blue-100',
            'icon_color' => 'text-blue-600',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />'
        ],
    ];

    $config = $types[$type] ?? $types['info'];
@endphp

<div x-data="{ show: true }" 
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @if($dismissible)
     x-init="setTimeout(() => show = false, {{ $duration }})"
     @endif
     class="pointer-events-auto w-full overflow-hidden rounded-lg {{ $config['bg'] }} border {{ $config['border'] }} shadow-lg ring-1 ring-black ring-opacity-5"
     style="display: none;">
    <div class="p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <div class="flex h-10 w-10 items-center justify-center rounded-full {{ $config['icon_bg'] }}">
                    <svg class="h-6 w-6 {{ $config['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        {!! $config['icon'] !!}
                    </svg>
                </div>
            </div>
            <div class="ml-3 flex-1 pt-0.5">
                @if($title)
                    <p class="text-sm font-semibold {{ $config['text'] }}">
                        {{ $title }}
                    </p>
                @endif
                <p class="text-sm @if($title) mt-1 @else font-medium @endif {{ $config['text'] }}">
                    {{ $message ?? $slot }}
                </p>
            </div>
            @if($dismissible)
            <div class="ml-4 flex flex-shrink-0">
                <button @click="show = false" 
                        type="button" 
                        class="inline-flex rounded-md {{ $config['bg'] }} {{ $config['text'] }} hover:{{ $config['text'] }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-{{ explode('-', $config['icon_color'])[1] }}-50 focus:ring-{{ explode('-', $config['icon_color'])[1] }}-600 transition-all duration-200">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
            @endif
        </div>
    </div>
</div>