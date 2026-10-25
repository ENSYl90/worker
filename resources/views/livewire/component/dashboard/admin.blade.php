<div>
    <x-baseview title="{{ __('Dashboard') }}">

        {{-- Info cards --}}
        <div class="grid gap-6 mt-10 md:grid-cols-2 lg:grid-cols-4">

            {{-- Orders --}}
            <x-dashboard-card bg="bg-blue-500" title="{{ __('TOTAL ORDERS') }}" value="{{ $this->totalOrders }}">
                <x-heroicon-s-shopping-bag class="w-8 h-8" />
            </x-dashboard-card>

            {{-- Earning --}}
            <x-dashboard-card bg="bg-emerald-500" title="{{ __('TOTAL EARNINGS') }}"
                value="{{ setting('currency') }} {{ $this->totalEarnings }}">
                <x-heroicon-s-cash class="w-8 h-8" />
            </x-dashboard-card>

            {{-- Total Vendors --}}
            <x-dashboard-card bg="bg-orange-500" title="{{ __('TOTAL VENDORS') }}" value="{{ $this->totalVendors }}">
                <x-heroicon-s-cake class="w-8 h-8" />
            </x-dashboard-card>

            {{-- Users --}}
            <x-dashboard-card bg="bg-purple-500" title="{{ __('TOTAL CLIENTS') }}" value="{{ $this->totalClients }}">
                <x-heroicon-s-users class="w-8 h-8" />
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

        {{-- Main Charts --}}
        <div class="grid gap-6 mt-10 lg:grid-cols-2">

            {{-- Earnings Chart --}}
            <x-dashboard-chart title="{{ __('Earnings Over Time') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->earningChart" />
            </x-dashboard-chart>

            {{-- Orders Chart --}}
            <x-dashboard-chart title="{{ __('Total Orders (Current Year)') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->ordersChart" />
            </x-dashboard-chart>
        </div>

        <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-6">

            <x-dashboard-chart title="{{ __('User Distribution') }}">
                <livewire:livewire-pie-chart :pie-chart-model="$this->userRolesChart" />
            </x-dashboard-chart>

            <x-dashboard-chart title="{{ __('Order Status Breakdown') }}">
                <livewire:livewire-pie-chart :pie-chart-model="$this->orderStatusChart" />
            </x-dashboard-chart>

            {{-- Top Sale Days --}}
            <x-dashboard-chart title="{{ __('Orders by Day of Week') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->topSaleDaysChart" />
            </x-dashboard-chart>

            {{-- Top Sale Timing --}}
            <x-dashboard-chart title="{{ __('Peak Ordering Times (Last 30 Days)') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->topSaleTimingChart" />
            </x-dashboard-chart>
        </div>

        {{-- Data Lists --}}
        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- top selling --}}
            <div wire:init="fetchTopSellingVendors" class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Top Selling Vendors') }}</h3>
                <div class="space-y-4">
                    @foreach ($topSellingVendors ?? [] as $vendor)
                        <a href="{{ route('vendor.details', ['id' => $vendor->id]) }}" class="flex items-center group">
                            <img src="{{ $vendor->logo }}"
                                class="w-10 h-10 rounded-lg object-cover mr-3 shadow-sm group-hover:shadow transition-shadow" />
                            <div class="overflow-hidden">
                                <p
                                    class="text-sm font-medium text-gray-900 truncate group-hover:text-primary-600 transition-colors">
                                    {{ $vendor->name }}</p>
                                <p class="text-xs text-gray-500">{{ $vendor->successful_sales_count ?? 0 }}
                                    {{ __('Orders') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Top rated Vendors --}}
            <div wire:init="fetchTopRatedVendors" class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Top Rated Vendors') }}</h3>
                <div class="space-y-4">
                    @foreach ($topRatedVendors ?? [] as $vendor)
                        <a href="{{ route('vendor.details', ['id' => $vendor->id]) }}" class="flex items-center group">
                            <img src="{{ $vendor->logo }}"
                                class="w-10 h-10 rounded-lg object-cover mr-3 shadow-sm group-hover:shadow transition-shadow" />
                            <div class="overflow-hidden">
                                <p
                                    class="text-sm font-medium text-gray-900 truncate group-hover:text-primary-600 transition-colors">
                                    {{ $vendor->name }}</p>
                                <div class="flex items-center mt-0.5">
                                    <x-heroicon-s-star class="w-3 h-3 text-yellow-400 mr-1" />
                                    <span
                                        class="text-xs text-gray-600 font-semibold">{{ number_format($vendor->rating ?? 0, 1) }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Top customers --}}
            <div wire:init="fetchTopCustomers" class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Top Customers') }}</h3>
                <div class="space-y-4">
                    @foreach ($topCustomers ?? [] as $user)
                        <a href="{{ route('users.details', ['id' => $user->uuid ?? $user->id]) }}" class="flex items-center group">
                            <img src="{{ $user->photo }}"
                                class="w-10 h-10 rounded-lg object-cover mr-3 shadow-sm group-hover:shadow transition-shadow" />
                            <div class="overflow-hidden">
                                <p
                                    class="text-sm font-medium text-gray-900 truncate group-hover:text-primary-600 transition-colors">
                                    {{ $user->name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $user->successful_orders_count ?? 0 }} / {{ $user->orders_count ?? 0 }}
                                    {{ __('Succeeded') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Top selling products --}}
            <div wire:init="fetchTopSellingProducts" class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Top Products') }}</h3>
                <div class="space-y-4">
                    @foreach ($topSellingProducts ?? [] as $product)
                        <a href="{{ route('product.details', ['id' => $product->id]) }}" class="flex items-center group">
                            <img src="{{ $product->photo }}"
                                class="w-10 h-10 rounded-lg object-cover mr-3 shadow-sm group-hover:shadow transition-shadow" />
                            <div class="overflow-hidden">
                                <p
                                    class="text-sm font-medium text-gray-900 truncate group-hover:text-primary-600 transition-colors">
                                    {{ $product->name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $product->successful_sales_count ?? 0 }} {{ __('Sold') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="h-20"></div>

    </x-baseview>
</div>
