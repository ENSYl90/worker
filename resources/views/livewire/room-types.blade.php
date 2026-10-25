@section('title', __('Room Types'))
<div>
    <x-baseview title="{{ __('Room Types') }}" :showNew="true">
        <livewire:tables.room-type-table />
    </x-baseview>

    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Save') }}" action="save">
            <p class="text-xl font-semibold">{{ __('New Room Type') }}</p>

            <x-input title="{{ __('Name') }}" name="name" placeholder="{{ __('Name') }}" />

            <x-textarea title="{{ __('Description') }}" name="description" placeholder="{{ __('Description') }}" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input title="{{ __('Max Guests') }}" name="max_guests" type="number"
                    placeholder="{{ __('Max Guests') }}" />
                <x-input title="{{ __('Base Price') }}" name="base_price" type="number" step="0.01"
                    placeholder="{{ __('Base Price') }}" />
            </div>

            <x-input title="{{ __('Total Rooms') }}" name="number_of_rooms" type="number"
                placeholder="{{ __('No. of rooms') }}" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input title="{{ __('Bedrooms') }}" name="bedrooms" type="number"
                    placeholder="{{ __('Bedrooms') }}" />
                <x-input title="{{ __('Bathrooms') }}" name="bathrooms" type="number"
                    placeholder="{{ __('Bathrooms Count') }}" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <x-select title="{{ __('Size Unit') }}" name="size_unit" :options="[
        ['id' => 'sqft', 'name' => 'Square Feet (sqft)'],
        ['id' => 'sqm', 'name' => 'Square Meters (sqm)']
    ]" />

    <x-input title="{{ __('Size') }}" name="size" type="number" step="0.01"
                    placeholder="{{ __('Size') }}" />

            </div>
        </x-modal>
    </div>

    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="save">
            <p class="text-xl font-semibold">{{ __('Update Room Type') }}</p>

            <x-input title="{{ __('Name') }}" name="name" placeholder="{{ __('Name') }}" />

            <x-textarea title="{{ __('Description') }}" name="description" placeholder="{{ __('Description') }}" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input title="{{ __('Max Guests') }}" name="max_guests" type="number"
                    placeholder="{{ __('Max Guests') }}" />
                <x-input title="{{ __('Base Price') }}" name="base_price" type="number" step="0.01"
                    placeholder="{{ __('Base Price') }}" />
            </div>

            <x-input title="{{ __('Total Rooms') }}" name="number_of_rooms" type="number"
                placeholder="{{ __('No. of rooms') }}" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input title="{{ __('Bedrooms') }}" name="bedrooms" type="number"
                    placeholder="{{ __('Bedrooms') }}" />
                <x-input title="{{ __('Bathrooms') }}" name="bathrooms" type="number"
                    placeholder="{{ __('Bathrooms Count') }}" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <x-select title="{{ __('Size Unit') }}" name="size_unit" :options="[
        ['id' => 'sqft', 'name' => 'Square Feet (sqft)'],
        ['id' => 'sqm', 'name' => 'Square Meters (sqm)']
    ]" />

    <x-input title="{{ __('Size') }}" name="size" type="number" step="0.01"
                    placeholder="{{ __('Size') }}" />

            </div>
        </x-modal>
    </div>
</div>