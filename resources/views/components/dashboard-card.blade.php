@props(['bg' => 'bg-primary-500', 'title', 'value'])

<div class="flex items-center p-6 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
    <div class="p-3 mr-4 {{ $bg }} bg-opacity-10 rounded-lg text-primary-600">
        {{ $slot }}
    </div>
    <div>
        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ $title }}</p>
        <p class="text-2xl font-bold text-gray-800">{{ $value }}</p>
    </div>
</div>
