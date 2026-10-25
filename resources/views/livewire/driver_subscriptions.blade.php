@section('title', __('Driver Subscriptions'))
<div>

    <x-baseview title="{{ __('Driver Subscriptions') }}" :showNew="true">
        <livewire:tables.driver-subscription-table />
    </x-baseview>

    <div x-data="{ open: @entangle('showCreate'), type: @entangle('type') }">
        <x-modal :clickAway="false" confirmText="{{ __('Save') }}" action="save">

            <p class="text-xl font-semibold">{{ __('New Driver Subscription') }}</p>
            <x-input title="{{ __('Name') }}" name="name" />
            <x-select title="{{ __('Type') }}" :options="$types" name="type" :defer="false" />

            <div x-show="type === 'time'">
                <x-input title="{{ __('Days') }}" name="days" type="number"
                    hint="{{ __('Required for time based subscriptions') }}" />
            </div>


            <div x-show="type === 'orders'">
                <x-input title="{{ __('Order Limit') }}" name="order_limit" type="number"
                    hint="{{ __('Required for order based subscriptions') }}" />
            </div>

            <x-input title="{{ __('Amount') }}" name="amount" type="number" step="0.01" />
            <x-checkbox title="{{ __('Active') }}" name="isActive" />

        </x-modal>
    </div>

    <div x-data="{ open: @entangle('showEdit'), type: @entangle('type') }">
        <x-modal :clickAway="false" confirmText="{{ __('Update') }}" action="update">

            <p class="text-xl font-semibold">{{ __('Edit Driver Subscription') }}</p>
            <x-input title="{{ __('Name') }}" name="name" />
            <x-select title="{{ __('Type') }}" :options="$types" name="type" :defer="false" />
            <div x-show="type === 'time'">
                <x-input title="{{ __('Days') }}" name="days" type="number"
                    hint="{{ __('Required for time based subscriptions') }}" />
            </div>
            <div x-show="type === 'orders'">
                <x-input title="{{ __('Order Limit') }}" name="order_limit" type="number"
                    hint="{{ __('Required for order based subscriptions') }}" />
            </div>
            <x-input title="{{ __('Amount') }}" name="amount" type="number" step="0.01" />
            <x-checkbox title="{{ __('Active') }}" name="isActive" />

        </x-modal>
    </div>

</div>
