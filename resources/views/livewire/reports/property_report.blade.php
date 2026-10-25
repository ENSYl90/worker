@section('title', __('Property Report'))
<div>

    <x-baseview title="">
        {{-- Info cards --}}
        <div class="grid gap-6 mt-10 md:grid-cols-2 lg:grid-cols-3">

            {{-- propertiesChart --}}
            <x-dashboard-chart>
                <livewire:livewire-pie-chart :pie-chart-model="$propertiesChart" />
            </x-dashboard-chart>

            {{-- topPropertyTypeChart --}}
            <x-dashboard-chart>
                <livewire:livewire-pie-chart :pie-chart-model="$topPropertyTypeChart" />
            </x-dashboard-chart>

            <!-- top hosts -->
            @role('admin')
            <x-dashboard-chart>
                <livewire:livewire-pie-chart :pie-chart-model="$topHostsChart" />
            </x-dashboard-chart>
            @endrole


        </div>
    </x-baseview>
    <div class="mt-12"></div>

    <x-baseview title="{{ __('Property Report') }}">
        <livewire:tables.reports.property-report-table />
    </x-baseview>

</div>
@push('scripts')
    @livewireChartsScripts
@endpush