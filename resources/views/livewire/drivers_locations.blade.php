@section('title', __('Drivers Locations'))
<div>

    <x-baseview title="{{ __('Drivers') }}">
        <x-slot name="newBtn">
            <x-buttons.primary wireClick="cleanDeletedDrivers">
                <div class="flex items-center gap-2">
                    <x-tabler-trash />
                    <span>{{ __('Clean Deleted Drivers') }}</span>
                </div>
            </x-buttons.primary>
        </x-slot>
        @if(!isQueueRedis())
            <livewire:tables.drivers-locations-table />
        @else
            <div>
                {{-- manual table --}}
                <div class="mt-8 overflow-hidden bg-white border border-gray-100 shadow-sm rounded-2xl">
                    <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">

                        <div class="w-full">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Real-time Drivers') }}</h3>
                            <p class="text-xs text-gray-500 mt-1 font-medium">
                                {{ __('Direct overview of drivers currently active in the system storage.') }}
                            </p>
                        </div>
                        {{-- earch input section --}}
                        <div class="">
                            <input type="text" wire:model.debounce.600ms="keyword"
                                placeholder="{{ __("Search Driver") }}" title="{{ __("Search") }}"
                                class="rounded px-2 py-2 border border-gray-300 text-sm" />
                        </div>

                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50 border text-black font-medium">
                                    {{-- s/n --}}
                                    <th class="px-6 py-4">
                                        {{ __('S/N') }}
                                    </th>
                                    <th class="px-6 py-4">
                                        {{ __('Driver') }}
                                    </th>
                                    <th class="px-6 py-4">
                                        {{ __('Coordinates') }}
                                    </th>
                                    <th class="px-6 py-4">
                                        {{ __('Status') }}
                                    </th>
                                    <th class="px-6 py-4">
                                        {{ __('Free') }}
                                    </th>
                                    <th class="px-6 py-4">
                                        {{ __('Last Synced') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($this->drivers ?? [] as $driver)
                                    <tr class="hover:bg-gray-50/50 transition-colors group">

                                        <td class="px-6 py-4">
                                            {{ ($this->drivers->currentPage() - 1) * $this->drivers->perPage() + $loop->index + 1 }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $driver['name'] ?? ($driver->name ?? __('Unknown Driver')) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center text-sm font-medium text-gray-600">
                                                @php
                                                    $driverLocationLink = $driver['lat'] . "," . $driver['lng'];
                                                @endphp
                                                <a href='https://www.google.com/maps?q={{ $driverLocationLink }}'
                                                    target='_blank' class="underline text-gray-500">
                                                    {{ $driverLocationLink }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $text = $driver['is_online'] ? __('Online') : __('Offline');
                                                $textColor = $driver['is_online'] ? 'text-white' : 'text-white';
                                                $bgColor = $driver['is_online'] ? 'bg-green-500' : 'bg-red-500';
                                                $data = [
                                                    "text" => $text,
                                                    "textColor" => $textColor,
                                                    "bgColor" => $bgColor,
                                                ];
                                            @endphp
                                            {!! view('components.table.plain_chip', $data)->render() !!}
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                if ($driver->assigned_orders > 0) {
                                                    $text = __("Assigned Order");
                                                    $textColor = "text-white";
                                                    $bgColor = "border-0 shadow-sm bg-green-500";
                                                } else {
                                                    $text = __("Free");
                                                    $textColor = "text-white";
                                                    $bgColor = "border-0 shadow-sm bg-red-500";
                                                }
                                                $data = [
                                                    "text" => $text,
                                                    "textColor" => $textColor,
                                                    "bgColor" => $bgColor,
                                                ];
                                            @endphp
                                            {!! view('components.table.plain_chip', $data)->render() !!}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $driver['updated_at'] ?? ($driver->updated_at ?? __('Recent')) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center max-w-xs mx-auto">
                                                <div
                                                    class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                                    <x-heroicon-o-map class="w-8 h-8 text-gray-300" />
                                                </div>
                                                <h4 class="text-gray-900 font-bold">{{ __('No Online Drivers') }}</h4>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    {{ __('There are currently no active driver locations stored in the real-time buffer.') }}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Pagination --}}
                <div class="mt-6">
                    {{-- @if(!empty($this->drivers ?? [])) --}}
                    {{ $this->drivers->links() ?? [] }}
                    {{-- @endif --}}
                </div>
            </div>
        @endif
    </x-baseview>

</div>