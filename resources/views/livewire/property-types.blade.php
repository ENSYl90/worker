@section('title', __('Property Types'))
<div>
    <x-baseview title="{{ __('Property Types') }}" :showNew="true">
        <livewire:tables.property-type-table />
    </x-baseview>

    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Save') }}" action="save">
            <p class="text-xl font-semibold">{{ __('New Property Type') }}</p>

            <x-input title="{{ __('Name') }}" name="name" placeholder="{{ __('Cabins, Beachfront, etc.') }}" />

            <x-media-upload title="{{ __('Icon Image') }}" name="photo" preview="{{ $photo }}"
                note="{{ __('Upload an icon image for this property type') }}" />
        </x-modal>
    </div>

    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="save">
            <p class="text-xl font-semibold">{{ __('Update Property Type') }}</p>

            <x-input title="{{ __('Name') }}" name="name" />

            <x-media-upload title="{{ __('Icon Image') }}" name="photo" preview="{{ $selectedModel->photo ?? '' }}"
                note="{{ __('Upload an icon image for this property type') }}" />
        </x-modal>
    </div>
</div>