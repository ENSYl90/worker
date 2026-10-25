@section('title', __('Taxi Zones'))
<div>


    <x-baseview title="{{ __('Taxi Zones') }}" :showNew="true">
        <livewire:tables.taxi-zone-table />
    </x-baseview>


    {{-- new form --}}
    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal-lg confirmText="{{ __('Save') }}" action="save" :clickAway="false">
            <p class="text-xl font-semibold">{{ __('New Delivery Zone') }}</p>
            <x-input title="{{ __('Name') }}" name="name" placeholder="" />
            <x-map-view id="map" mode="draw" />
            <x-textarea title="{{ __('Coordinates') }}" name="coordinates" placeholder="" disable="true" />
            <x-checkbox title="{{ __('Active') }}" name="is_active" :defer="false" />
        </x-modal-lg>
    </div>

    {{-- edit form --}}
    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal-lg confirmText="{{ __('Save') }}" action="update" :clickAway="false">
            <p class="text-xl font-semibold">{{ __('Update Delivery Zone') }}</p>
            <x-input title="{{ __('Name') }}" name="name" placeholder="" />
            <x-map-view id="editMap" mode="edit" />
            <x-textarea title="{{ __('Coordinates') }}" name="coordinates" placeholder="" disable="true" />
            <x-checkbox title="{{ __('Active') }}" name="is_active" :defer="false" />
        </x-modal-lg>
    </div>


</div>

