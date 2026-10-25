<div>
    <x-baseview title="{{ __('Fleet Dashboard') }}">
        <div class="mt-6 rounded-lg border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-medium text-primary-600">{{ __('Fleet Manager') }}</p>
                    <h2 class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ $this->fleet->name ?? __('Assigned Fleet') }}
                    </h2>
                </div>
                <a href="{{ route('fleet.report') }}"
                    class="inline-flex h-11 items-center justify-center rounded-md bg-primary-600 px-4 text-sm font-semibold text-theme transition duration-200 hover:bg-primary-700 focus:outline-none focus:shadow-outline-primary">
                    {{ __('View fleet orders') }}
                </a>
            </div>
        </div>

        <div class="grid gap-6 mt-10 md:grid-cols-2 lg:grid-cols-4">
            <x-dashboard-card bg="bg-blue-500" title="{{ __('FLEET ORDERS') }}" value="{{ $this->totalOrders }}">
                <x-heroicon-s-shopping-bag class="w-8 h-8" />
            </x-dashboard-card>

            <x-dashboard-card bg="bg-emerald-500" title="{{ __('DELIVERED ORDERS') }}" value="{{ $this->deliveredOrders }}">
                <x-heroicon-s-check-circle class="w-8 h-8" />
            </x-dashboard-card>

            <x-dashboard-card bg="bg-orange-500" title="{{ __('ACTIVE DRIVERS') }}" value="{{ $this->activeDrivers }}">
                <x-tabler-steering-wheel class="w-8 h-8" />
            </x-dashboard-card>

            <x-dashboard-card bg="bg-purple-500" title="{{ __('VEHICLES') }}" value="{{ $this->totalVehicles }}">
                <x-heroicon-s-truck class="w-8 h-8" />
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
            <x-dashboard-chart title="{{ __('Fleet Orders (Current Year)') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->ordersChart" />
            </x-dashboard-chart>

            <x-dashboard-chart title="{{ __('Fleet Order Status') }}">
                <livewire:livewire-pie-chart :pie-chart-model="$this->orderStatusChart" />
            </x-dashboard-chart>
        </div>

        <div class="grid gap-6 mt-10 lg:grid-cols-2">
            <x-dashboard-chart title="{{ __('Top Drivers') }}">
                <livewire:livewire-column-chart :column-chart-model="$this->topDriversChart" />
            </x-dashboard-chart>

            <div wire:init="fetchTopDrivers" class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Driver Performance') }}</h3>
                <div class="space-y-4">
                    @forelse ($topDrivers ?? [] as $driver)
                        <div class="flex items-center">
                            <img src="{{ $driver->photo }}"
                                class="w-10 h-10 rounded-lg object-cover mr-3 shadow-sm" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-900">{{ $driver->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $driver->delivered_orders_count ?? 0 }} / {{ $driver->driver_orders_count ?? 0 }}
                                    {{ __('Succeeded') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('No driver activity yet') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div wire:init="fetchRecentOrders" class="mt-10 bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Recent Fleet Orders') }}</h3>
                <a href="{{ route('fleet.report') }}" class="text-sm font-semibold text-primary-600 hover:underline">
                    {{ __('View all') }}
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold uppercase text-gray-500">
                            <th class="py-3 pr-4">{{ __('Code') }}</th>
                            <th class="py-3 pr-4">{{ __('Customer') }}</th>
                            <th class="py-3 pr-4">{{ __('Driver') }}</th>
                            <th class="py-3 pr-4">{{ __('Status') }}</th>
                            <th class="py-3 pr-4">{{ __('Total') }}</th>
                            <th class="py-3">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentOrders ?? [] as $order)
                            <tr>
                                <td class="py-3 pr-4 font-semibold text-gray-900">{{ $order->code }}</td>
                                <td class="py-3 pr-4 text-gray-700">{{ $order->user->name ?? '-' }}</td>
                                <td class="py-3 pr-4 text-gray-700">{{ $order->driver->name ?? '-' }}</td>
                                <td class="py-3 pr-4 text-gray-700">{{ \Str::ucfirst($order->status) }}</td>
                                <td class="py-3 pr-4 text-gray-700">{{ setting('currency') }} {{ currencyValueFormat($order->total) }}</td>
                                <td class="py-3 text-gray-500">{{ $order->formatted_date }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-gray-500">{{ __('No fleet orders yet') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="h-20"></div>
    </x-baseview>
</div>
