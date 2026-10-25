@section('title', __('Booking Orders'))
<div>
    <x-baseview title="{{ __('Booking Orders') }}">
        <livewire:tables.booking-order-table />
    </x-baseview>
</div>