@section('title', __('Booking Fee Types'))
<div>
    <x-baseview title="{{ __('Booking Fee Types') }}" :showNew="true">
        <livewire:tables.booking-fee-type-table />
    </x-baseview>

    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal confirmText="{{ __('Save') }}" action="save">
            <p class="text-xl font-semibold">{{ __('New Booking Fee Type') }}</p>

            <x-input title="{{ __('Name') }}" name="name" placeholder="{{ __('Cleaning Fee, Service Fee, etc.') }}" />

            <x-textarea title="{{ __('Description') }}" name="description" placeholder="{{ __('Fee description') }}" />

            <x-select title="{{ __('Calculation Type') }}" name="calculation_type" :options="$this->calculationTypes" />

            <x-checkbox title="{{ __('Is Mandatory') }}" name="is_mandatory" description="{{ __('This fee will be automatically applied to all bookings') }}" />

            <x-checkbox title="{{ __('Is Taxable') }}" name="is_taxable" description="{{ __('This fee will be included in tax calculations') }}" />

            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <h4 class="font-semibold text-blue-900 mb-2">{{ __('Calculation Types Explained:') }}</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li><strong>{{ __('Fixed Amount') }}:</strong> {{ __('A flat fee (e.g., $50 cleaning fee)') }}</li>
                    <li><strong>{{ __('Percentage') }}:</strong> {{ __('A percentage of the total nights cost (e.g., 10% service fee)') }}</li>
                    <li><strong>{{ __('Per Night') }}:</strong> {{ __('Multiplied by number of nights (e.g., $5 per night)') }}</li>
                    <li><strong>{{ __('Per Guest') }}:</strong> {{ __('Multiplied by number of guests (e.g., $10 per guest)') }}</li>
                </ul>
            </div>
        </x-modal>
    </div>

    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal confirmText="{{ __('Update') }}" action="save">
            <p class="text-xl font-semibold">{{ __('Update Booking Fee Type') }}</p>

            <x-input title="{{ __('Name') }}" name="name" placeholder="{{ __('Cleaning Fee, Service Fee, etc.') }}" />

            <x-textarea title="{{ __('Description') }}" name="description" placeholder="{{ __('Fee description') }}" />

            <x-select title="{{ __('Calculation Type') }}" name="calculation_type" :options="$this->calculationTypes" />

            <x-checkbox title="{{ __('Is Mandatory') }}" name="is_mandatory" description="{{ __('This fee will be automatically applied to all bookings') }}" />

            <x-checkbox title="{{ __('Is Taxable') }}" name="is_taxable" description="{{ __('This fee will be included in tax calculations') }}" />

            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <h4 class="font-semibold text-blue-900 mb-2">{{ __('Calculation Types Explained:') }}</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li><strong>{{ __('Fixed Amount') }}:</strong> {{ __('A flat fee (e.g., $50 cleaning fee)') }}</li>
                    <li><strong>{{ __('Percentage') }}:</strong> {{ __('A percentage of the total nights cost (e.g., 10% service fee)') }}</li>
                    <li><strong>{{ __('Per Night') }}:</strong> {{ __('Multiplied by number of nights (e.g., $5 per night)') }}</li>
                    <li><strong>{{ __('Per Guest') }}:</strong> {{ __('Multiplied by number of guests (e.g., $10 per guest)') }}</li>
                </ul>
            </div>
        </x-modal>
    </div>
</div>
