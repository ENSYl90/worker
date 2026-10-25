<div>
    <x-baseview title="{{ __('Dashboard') }}">

        <div class="grid gap-6 mt-10 md:grid-cols-2 lg:grid-cols-4">
            <x-dashboard-card bg="bg-blue-500" title="{{ __('TOTAL ORDERS') }}" value="{{ $this->totalOrders }}">
                <x-heroicon-s-shopping-bag class="w-8 h-8" />
            </x-dashboard-card>

            <x-dashboard-card bg="bg-emerald-500" title="{{ __('TOTAL EARNINGS') }}"
                value="{{ setting('currency') }} {{ $this->totalEarnings }}">
                <x-heroicon-s-cash class="w-8 h-8" />
            </x-dashboard-card>
        </div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <p class="rounded-md border border-gray-100 bg-white px-3 py-2 text-xs font-medium text-gray-500 shadow-sm">
                {{ __('Last live data fetch') }}: {{ $this->liveDataFetchedAt }}
            </p>
            <button type="button" wire:click="refreshDashboardData" wire:loading.attr="disabled"
                class="inline-flex h-9 items-center justify-center rounded-md border border-primary-200 bg-primary-50 px-3 text-xs font-semibold text-primary-700 transition duration-200 hover:bg-primary-100 focus:outline-none focus:shadow-outline-primary disabled:cursor-not-allowed disabled:opacity-60">
                <span wire:loading.remove wire:target="refreshDashboardData">{{ __('Force live fetch') }}</span>
                <span wire:loading wire:target="refreshDashboardData">{{ __('Fetching...') }}</span>
            </button>
        </div>

        <div class="grid gap-6 mt-10 lg:grid-cols-2">
            <x-dashboard-chart title="{{ __('Earnings Over Time') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->earningChart" />
            </x-dashboard-chart>

            <x-dashboard-chart title="{{ __('Total Orders (Current Year)') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->ordersChart" />
            </x-dashboard-chart>
        </div>

        <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-dashboard-chart title="{{ __('Orders by Day of Week') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->topSaleDaysChart" />
            </x-dashboard-chart>

            <x-dashboard-chart title="{{ __('Peak Ordering Times (Last 30 Days)') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->topSaleTimingChart" />
            </x-dashboard-chart>
        </div>

        <div class="h-20"></div>

    </x-baseview>
</div>
