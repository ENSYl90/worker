@props([
    'id' => 'map',
    'mode' => 'draw',
    'height' => 'h-72',
])

<p class="mt-1 text-xs text-gray-500">
    @if ($mode === 'edit')
        {{ __('Drag the existing points to adjust the zone, or click on the map to draw a new shape and replace it.') }}
    @else
        {{ __('Click on the map to place each corner of the zone. Click the first point again to close the shape.') }}
    @endif
</p>
<div wire:ignore id="{{ $id }}" class="my-4 {{ $height }}"></div>
<button type="button" onclick="livewire.emit('resetDrawing')" class="mb-2 text-xs underline text-primary-500">
    {{ __('Reset drawing') }}
</button>
@pushOnce('scripts')
    <x-google-maps-script />
    <script src="{{ asset('js/delivery-zone.js') }}"></script>
@endPushOnce