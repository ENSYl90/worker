{{-- Modern Unified Order Details Component --}}
<div class="w-full space-y-6">

    {{-- Order Status Card --}}
    <div class="bg-primary-500 rounded-lg shadow-lg p-6 text-theme">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs opacity-80 uppercase tracking-wide">{{ __('Order Code') }}</p>
                <p class="text-2xl font-bold">#{{ $selectedModel->code }}</p>
            </div>
            @if($selectedModel->verification_code)
                <div>
                    <p class="text-xs opacity-80 uppercase tracking-wide">{{ __('Verification Code') }}</p>
                    <p class="text-xl font-semibold">{{ $selectedModel->verification_code }}</p>
                </div>
            @endif
            <div>
                <p class="text-xs opacity-80 uppercase tracking-wide">{{ __('Status') }}</p>
                <p class="text-lg font-semibold">{{ ucfirst(__($selectedModel->status ?? '')) }}</p>
            </div>
            <div>
                <p class="text-xs opacity-80 uppercase tracking-wide">{{ __('Payment Status') }}</p>
                <div class="flex items-center mt-1">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white text-primary-600">
                        {{ ucfirst(__($selectedModel->payment_status ?? '')) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Main Details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Customer Information --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <x-tabler-user class="w-5 h-5 mr-2 text-primary-500" />
                    {{ $selectedModel->is_package ? __('Sender Information') : __('Customer Information') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">{{ __('Name') }}</p>
                        <p class="text-sm font-medium text-gray-900">{{ $selectedModel->user->name ?? '--' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">{{ __('Phone') }}</p>
                        <p class="text-sm font-medium text-gray-900">{{ $selectedModel->user->phone ?? '--' }}</p>
                    </div>
                    @if($selectedModel->user->email)
                        <div>
                            <p class="text-xs text-gray-500 uppercase">{{ __('Email') }}</p>
                            <p class="text-sm font-medium text-gray-900">{{ $selectedModel->user->email ?? '--' }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Delivery/Location Information --}}
            @if($selectedModel->order_type === 'taxi' && $selectedModel->taxi_order)
                {{-- Taxi Route Information --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-map-2 class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Trip Details') }}
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                                    <x-tabler-map-pin class="w-5 h-5 text-primary-600" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase">{{ __('Pickup Address') }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedModel->taxi_order->pickup_address }}</p>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                                    <x-tabler-map-pin class="w-5 h-5 text-primary-600" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 uppercase">{{ __('Dropoff Address') }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedModel->taxi_order->dropoff_address }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 pt-2 border-t">
                            <div class="flex-shrink-0">
                                <x-tabler-car class="w-5 h-5 text-primary-500" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">{{ __('Vehicle Type') }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedModel->taxi_order->vehicle_type->name ?? '--' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($selectedModel->order_type === 'package' || $selectedModel->order_type === 'parcel')
                {{-- Package Stops --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-map-pins class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Delivery Stops') }}
                    </h3>
                    <div class="space-y-3">
                        @foreach ($selectedModel->stops as $key => $stop)
                            <div class="border border-primary-200 rounded-lg p-4 hover:shadow-md hover:border-primary-300 transition-all bg-primary-50">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-bold text-white">{{ $key + 1 }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 space-y-2">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase">{{ __('Address') }}</p>
                                            <p class="text-sm font-medium text-gray-900">{{ $stop->delivery_address->address ?? '' }}</p>
                                        </div>
                                        @if($stop->name)
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase">{{ __('Recipient') }}</p>
                                                <p class="text-sm font-medium text-gray-900">{{ $stop->name }} @if($stop->phone)({{ $stop->phone }})@endif</p>
                                            </div>
                                        @endif
                                        @if($stop->note)
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase">{{ __('Note') }}</p>
                                                <p class="text-sm text-gray-700">{{ $stop->note }}</p>
                                            </div>
                                        @endif
                                        @if($stop->proof && !strpos($stop->proof, 'default.png'))
                                            <div>
                                                <p class="text-xs text-gray-500 uppercase mb-1">{{ __('Proof of Delivery') }}</p>
                                                <a href="{{ $stop->proof }}" target="_blank" class="inline-block">
                                                    <img src="{{ $stop->proof }}" class="w-20 h-20 rounded-md object-cover border-2 border-primary-200 hover:border-primary-400 transition-colors" />
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Regular Delivery Address --}}
                @if(!empty($selectedModel->delivery_address))
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <x-tabler-map-pin class="w-5 h-5 mr-2 text-primary-500" />
                            {{ __('Delivery Address') }}
                        </h3>
                        <div class="space-y-2">
                            <p class="font-medium text-gray-900">{{ $selectedModel->delivery_address->name ?? '' }}</p>
                            <p class="text-sm text-gray-700">{{ $selectedModel->delivery_address->address ?? '' }}</p>
                            @if($selectedModel->delivery_address->description)
                                <p class="text-sm text-gray-600">{{ $selectedModel->delivery_address->description }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

            {{-- Order Items --}}
            @if($selectedModel->order_type === 'service')
                {{-- Service Details --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-tools class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Service Details') }}
                    </h3>
                    <x-order.service :order="$selectedModel" />
                </div>
            @elseif($selectedModel->order_type === 'package' || $selectedModel->order_type === 'parcel')
                {{-- Package Details --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-package class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Package Details') }}
                    </h3>
                    <x-order.package :order="$selectedModel" />
                </div>
            @elseif($selectedModel->can_edit_products && $selectedModel->products && count($selectedModel->products) > 0)
                {{-- Product Items --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-shopping-bag class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Order Items') }}
                    </h3>
                    <x-order.products :products="$selectedModel->products" />
                </div>
            @endif

            {{-- Driver Information --}}
            @if($selectedModel->driver)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-user-circle class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Driver Information') }}
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">{{ __('Name') }}</p>
                            <p class="text-sm font-medium text-gray-900">{{ $selectedModel->driver->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">{{ __('Phone') }}</p>
                            <p class="text-sm font-medium text-gray-900">{{ $selectedModel->driver->phone }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Attachments & Photos --}}
            @if(($selectedModel->photo && !strpos($selectedModel->photo, 'default.png')) ||
                ($selectedModel->attachments && count($selectedModel->attachments) > 0) ||
                ($selectedModel->signature && !strpos($selectedModel->signature, 'default.png')) ||
                ($selectedModel->delivery_photo && !strpos($selectedModel->delivery_photo, 'default.png')))
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-photo class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Photos & Documents') }}
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        {{-- Order Photos/Attachments --}}
                        @if($selectedModel->attachments && count($selectedModel->attachments) > 0)
                            @foreach ($selectedModel->attachments as $attachment)
                                <div class="relative group">
                                    <a href="{{ $attachment['link'] }}" target="_blank">
                                        <img src="{{ $attachment['link'] }}" class="w-full h-32 object-cover rounded-lg border-2 border-transparent hover:border-primary-300 shadow hover:shadow-lg transition-all" />
                                    </a>
                                    <div class="absolute inset-0 bg-primary-500/0 group-hover:bg-primary-500/10 rounded-lg transition-all pointer-events-none"></div>
                                </div>
                            @endforeach
                        @elseif($selectedModel->photo && !strpos($selectedModel->photo, 'default.png'))
                            <div class="relative group">
                                <a href="{{ $selectedModel->photo }}" target="_blank">
                                    <img src="{{ $selectedModel->photo }}" class="w-full h-32 object-cover rounded-lg border-2 border-transparent hover:border-primary-300 shadow hover:shadow-lg transition-all" />
                                </a>
                                <div class="absolute inset-0 bg-primary-500/0 group-hover:bg-primary-500/10 rounded-lg transition-all pointer-events-none"></div>
                                <p class="text-xs text-gray-500 mt-1">{{ __('Order Photo') }}</p>
                            </div>
                        @endif

                        {{-- Signature --}}
                        @if($selectedModel->signature && !strpos($selectedModel->signature, 'default.png'))
                            <div class="relative group">
                                <a href="{{ $selectedModel->signature }}" target="_blank">
                                    <img src="{{ $selectedModel->signature }}" class="w-full h-32 object-cover rounded-lg border-2 border-transparent hover:border-primary-300 shadow hover:shadow-lg transition-all" />
                                </a>
                                <div class="absolute inset-0 bg-primary-500/0 group-hover:bg-primary-500/10 rounded-lg transition-all pointer-events-none"></div>
                                <p class="text-xs text-gray-500 mt-1">{{ __('Signature') }}</p>
                            </div>
                        @endif

                        {{-- Delivery Photo --}}
                        @if($selectedModel->delivery_photo && !strpos($selectedModel->delivery_photo, 'default.png'))
                            <div class="relative group">
                                <a href="{{ $selectedModel->delivery_photo }}" target="_blank">
                                    <img src="{{ $selectedModel->delivery_photo }}" class="w-full h-32 object-cover rounded-lg border-2 border-transparent hover:border-primary-300 shadow hover:shadow-lg transition-all" />
                                </a>
                                <div class="absolute inset-0 bg-primary-500/0 group-hover:bg-primary-500/10 rounded-lg transition-all pointer-events-none"></div>
                                <p class="text-xs text-gray-500 mt-1">{{ __('Delivery Photo') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- Right Column - Summary & Additional Info --}}
        <div class="space-y-6">

            {{-- Order Summary --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <x-tabler-calculator class="w-5 h-5 mr-2 text-primary-500" />
                    {{ __('Order Summary') }}
                </h3>
                <div class="space-y-3">
                    {{-- Payment Method --}}
                    <div class="flex items-center justify-between pb-3 border-b">
                        <span class="text-sm text-gray-600">{{ __('Payment Method') }}</span>
                        <span class="text-sm font-medium text-gray-900">{{ $selectedModel->payment_method->name ?? '--' }}</span>
                    </div>

                    {{-- Package specific: Payer --}}
                    @if($selectedModel->order_type === 'package' || $selectedModel->order_type === 'parcel')
                        <div class="flex items-center justify-between pb-3 border-b">
                            <span class="text-sm text-gray-600">{{ __('Payment By') }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $selectedModel->payer ?? 1 ? __('Sender') : __('Receiver') }}</span>
                        </div>
                    @endif

                    {{-- Taxi Trip Breakdown --}}
                    @if($selectedModel->order_type === 'taxi' && $selectedModel->taxi_order)
                        <div class="py-2 space-y-2">
                            <p class="text-xs font-semibold text-gray-700 uppercase">{{ __('Trip Breakdown') }}</p>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('Base Fare') }}</span>
                                <span class="font-medium">{{ currencyFormat($selectedModel->taxi_order->base_fare ?? 0) }}</span>
                            </div>
                            @if($selectedModel->taxi_order->trip_distance)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Distance') }} ({{ $selectedModel->taxi_order->trip_distance }} km)</span>
                                    <span class="font-medium">{{ currencyFormat($selectedModel->taxi_order->distance_fare ?? 0) }}</span>
                                </div>
                            @endif
                            @if($selectedModel->taxi_order->trip_time)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Time') }} ({{ $selectedModel->taxi_order->trip_time }} min)</span>
                                    <span class="font-medium">{{ currencyFormat($selectedModel->taxi_order->time_fare ?? 0) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="border-t pt-2"></div>
                    @endif

                    {{-- Driver Tip --}}
                    @if($selectedModel->tip > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('Driver Tip') }}</span>
                            <span class="font-medium">{{ currencyFormat($selectedModel->tip) }}</span>
                        </div>
                    @endif

                    {{-- Subtotal --}}
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ __('Subtotal') }}</span>
                        <span class="font-medium">{{ currencyFormat($selectedModel->sub_total) }}</span>
                    </div>

                    {{-- Discount --}}
                    @if($selectedModel->discount > 0)
                        <div class="flex justify-between text-sm text-primary-600">
                            <span>{{ __('Discount') }}</span>
                            <span class="font-medium">-{{ currencyFormat($selectedModel->discount) }}</span>
                        </div>
                    @endif

                    {{-- Delivery Fee --}}
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ __('Delivery Fee') }}</span>
                        <span class="font-medium">{{ currencyFormat($selectedModel->delivery_fee) }}</span>
                    </div>

                    {{-- Tax --}}
                    @if($selectedModel->tax > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('Tax') }}</span>
                            <span class="font-medium">{{ currencyFormat($selectedModel->tax) }}</span>
                        </div>
                    @endif

                    {{-- Additional Fees --}}
                    <x-order.fees :order="$selectedModel" />

                    {{-- Total --}}
                    <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                        <span class="text-lg font-bold text-gray-900">{{ __('Total') }}</span>
                        <span class="text-2xl font-bold text-primary-600">{{ currencyFormat($selectedModel->total) }}</span>
                    </div>
                </div>
            </div>

            {{-- Vendor Information --}}
            @if($selectedModel->vendor)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-building-store class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Vendor Information') }}
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">{{ __('Name') }}</p>
                            <p class="text-sm font-medium text-gray-900">{{ $selectedModel->vendor->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">{{ __('Address') }}</p>
                            <p class="text-sm text-gray-700">{{ $selectedModel->vendor->address ?? '--' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">{{ __('Phone') }}</p>
                            <p class="text-sm font-medium text-gray-900">{{ $selectedModel->vendor->phone ?? '--' }}</p>
                        </div>
                    </div>
                </div>
            @endif


            {{-- Status History --}}
            @if($selectedModel->statuses && count($selectedModel->statuses) > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-timeline class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Status History') }}
                    </h3>
                    <div class="relative">
                        <div class="absolute left-3.5 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                        <div class="space-y-4">
                            @foreach ($selectedModel->statuses->sortByDesc('created_at') as $index => $statusEntry)
                                <div class="relative flex items-start pl-10">
                                    <div class="absolute left-0 w-7 h-7 rounded-full flex items-center justify-center {{ $index === 0 ? 'bg-primary-500' : 'bg-gray-200' }}">
                                        <div class="{{ $index === 0 ? 'w-2.5 h-2.5 bg-white rounded-full' : 'w-2 h-2 bg-gray-400 rounded-full' }}"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold {{ $index === 0 ? 'text-primary-600' : 'text-gray-700' }}">
                                            {{ ucfirst(__($statusEntry->name)) }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ \Carbon\Carbon::parse($statusEntry->created_at)->format('M d, Y \a\t h:i a') }}
                                        </p>
                                        @if($statusEntry->reason)
                                            <p class="text-xs text-gray-500 italic mt-0.5">{{ $statusEntry->reason }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Order Timeline --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <x-tabler-clock class="w-5 h-5 mr-2 text-primary-500" />
                    {{ __('Timeline') }}
                </h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">{{ __('Created') }}</p>
                        <p class="text-sm font-medium text-gray-900">{{ $selectedModel->created_at->format('M d, Y \a\t h:i a') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">{{ __('Last Updated') }}</p>
                        <p class="text-sm font-medium text-gray-900">{{ $selectedModel->updated_at->format('M d, Y \a\t h:i a') }}</p>
                    </div>
                    @if($selectedModel->status == 'scheduled' && $selectedModel->pickup_date && $selectedModel->pickup_time)
                        @php
                            $scheduleDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $selectedModel->pickup_date . ' ' . $selectedModel->pickup_time);
                        @endphp
                        <div>
                            <p class="text-xs text-gray-500 uppercase">{{ __('Scheduled For') }}</p>
                            <p class="text-sm font-medium text-primary-600">{{ $scheduleDate->format('M d, Y \a\t h:i a') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes & Cancellation --}}
            @if($selectedModel->note || ($selectedModel->status == 'cancelled' && $selectedModel->reason))
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <x-tabler-notes class="w-5 h-5 mr-2 text-primary-500" />
                        {{ __('Notes') }}
                    </h3>
                    <div class="space-y-3">
                        @if($selectedModel->note)
                            <div>
                                <p class="text-xs text-gray-500 uppercase">{{ __('Order Note') }}</p>
                                <p class="text-sm text-gray-700 mt-1">{{ $selectedModel->note }}</p>
                            </div>
                        @endif
                        @if($selectedModel->status == 'cancelled' && $selectedModel->reason)
                            <div class="pt-3 border-t">
                                <p class="text-xs text-primary-600 uppercase font-semibold">{{ __('Cancellation Reason') }}</p>
                                <p class="text-sm text-gray-700 mt-1">{!! $selectedModel->reason !!}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>
