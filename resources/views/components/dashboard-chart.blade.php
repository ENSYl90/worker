@props(['title' => null, 'height' => 'h-120', 'bg' => 'bg-white'])

<div
    class="p-6 {{ $bg }} border border-gray-100 rounded-xl shadow-sm {{ $height }} max-h-[400px] flex flex-col overflow-hidden">
    @if($title)
        <div class="flex-none mb-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
        </div>
    @endif
    <div class="flex-grow w-full relative overflow-hidden min-h-0">
        {{ $slot }}
    </div>
</div>