<a class="flex items-center p-2 text-sm font-medium leading-5 text-center text-white transition-colors duration-150 border border-transparent rounded-lg {{ $bg ?? "bg-primary-600 active:bg-red-600 hover:bg-primary-700" }} focus:outline-none focus:shadow-outline-red"
    href="{{ $link ?? '#' }}" target="{{ $newTab ?? false ? '' : '_blank' }}" title="{{ $title ?? 'View' }}">

    {{ $slot ?? '' }}

</a>