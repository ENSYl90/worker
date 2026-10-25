@php
    $currentLocale = $lan ?? app()->getLocale();
@endphp

<div x-data="{ open: false }" class="relative inline-block text-left">
    <!-- Toggle Button -->
    <button type="button" @click="open = !open"
        class="bg-white border border-gray-300 text-sm px-2 py-1 rounded-md shadow-sm flex items-center space-x-2 hover:bg-gray-50">
        <span class="uppercase font-semibold">{{ strtoupper($currentLocale) }}</span>
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <!-- Dropdown -->
    <div x-cloak x-show="open" @click.away="open = false" x-transition
        class="absolute mt-2 w-40 bg-white border border-gray-200 rounded shadow-md z-50">
        @foreach ($languages as $language)
            <button type="button" wire:click="onLanSelected('{{ $language['id'] }}')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100
                            {{ $currentLocale === $language['id'] ? 'font-bold text-primary-600' : 'text-gray-700' }}">
                {{ $language['name'] }}
            </button>
        @endforeach
    </div>
</div>