@section('title', __('Amenities'))
<div>
    <x-baseview title="{{ __('Amenities') }}" :showNew="true">
        <livewire:tables.amenity-table />
    </x-baseview>

    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Save') }}" action="save">
            <p class="text-xl font-semibold">{{ __('New Amenity') }}</p>

            <x-input title="{{ __('Name') }}" name="name" placeholder="{{ __('WiFi, Pool, etc.') }}" />

            <x-media-upload title="{{ __('Icon Image') }}" name="photo" preview="{{ $photo }}"
                note="{{ __('Upload an icon image for this amenity') }}" />


        </x-modal>
    </div>

    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="save">
            <p class="text-xl font-semibold">{{ __('Update Amenity') }}</p>

            <x-input title="{{ __('Name') }}" name="name" placeholder="{{ __('WiFi, Pool, etc.') }}" />

            <x-media-upload title="{{ __('Icon Image') }}" name="photo" preview="{{ $selectedModel->photo ?? '' }}"
                note="{{ __('Upload an icon image for this amenity') }}" />

        </x-modal>
    </div>
</div>