@section('title', __('Property Report Reasons'))
<div>

    <x-baseview title="{{ __('Property Report Reasons') }}" :showNew="true">
        <livewire:tables.property-report-reason-table />
    </x-baseview>


    {{-- Create Modal --}}
    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Save') }}" action="save">
            <p class="text-xl font-semibold">{{ __('Create Property Report Reason') }}</p>
            <x-input title="{{ __('Reason') }}" name="reason" placeholder="{{ __('Enter reason') }}" />
            <div>
                <x-checkbox title="{{ __('Active') }}" name="is_active" :defer="false" />
            </div>
        </x-modal>
    </div>

    {{-- Edit Modal --}}
    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="update">
            <p class="text-xl font-semibold">{{ __('Edit Property Report Reason') }}</p>
            <x-input title="{{ __('Reason') }}" name="reason" placeholder="{{ __('Enter reason') }}" />
            <div>
                <x-checkbox title="{{ __('Active') }}" name="is_active" :defer="false" />
            </div>
        </x-modal>
    </div>
</div>