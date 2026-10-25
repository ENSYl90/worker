@section('title', __('Booking Details') . " #" . ($order->code ?? "404"))

<div>
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

        {{-- Main Card Container --}}
        <div class="bg-white shadow-lg rounded-2xl border border-gray-100 overflow-hidden">



            <div class="p-6 sm:p-10">

                {{-- Header Section --}}
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between mb-8 border-b border-gray-100 pb-6">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 mb-1 tracking-tight">
                            {{ __('Booking') }} #{{ $order->code }}
                        </h1>
                        <div class="flex items-center text-sm text-gray-500">
                            <span class="font-medium mr-2">{{ $order->created_at->format('F j, Y, g:i a') }}</span>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 space-y-2">
                        <!-- back to booking.orders route -->
                        <a href="{{ route('orders.booking') }}"
                            class="flex items-center gap-2 text-sm font-medium text-primary-600 hover:text-primary-700 transition duration-200">
                            <x-heroicon-o-arrow-left class="w-4 h-4" />
                            {{ __('Back to Bookings') }}
                        </a>
                        <div class="flex items-end">
                            <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold uppercase tracking-widest ring-1 ring-inset"
                                style="background-color: {{ setting("appColorTheme.{$order->status}Color", '#0099FF') }}15; color: {{ setting("appColorTheme.{$order->status}Color", '#0099FF') }}; ring-color: {{ setting("appColorTheme.{$order->status}Color", '#0099FF') }}30;">
                                {{ ucfirst(__($order->status)) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    {{-- LEFT COLUMN: Main Booking Info --}}
                    <div class="lg:col-span-2 space-y-8">

                        {{-- Trip Details --}}
                        <section>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Trip Details') }}</h3>
                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                                <div class="grid grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <span
                                            class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('Check-in') }}</span>
                                        <div class="mt-1 font-semibold text-gray-900 text-lg">
                                            {{ \Carbon\Carbon::parse($bookingOrder->check_in_date)->format('D, M j, Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $bookingOrder->check_in_time ?? '3:00 PM' }}
                                        </div>
                                    </div>
                                    <div>
                                        <span
                                            class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('Check-out') }}</span>
                                        <div class="mt-1 font-semibold text-gray-900 text-lg">
                                            {{ \Carbon\Carbon::parse($bookingOrder->check_out_date)->format('D, M j, Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $bookingOrder->check_out_time ?? '11:00 AM' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-6 border-t border-gray-200">
                                    <span
                                        class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('Total Duration') }}</span>
                                    <div class="mt-1 font-medium text-gray-900">
                                        {{ $bookingOrder->number_of_nights }} {{ __('nights') }}
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- Guest Info --}}
                        <section>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Guest Information') }}</h3>
                            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                                <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                                    <span class="font-medium text-gray-700">
                                        {{ $bookingOrder->number_of_guests }} {{ __('Guests') }}
                                        <span class="text-gray-500 text-sm font-normal ml-1">
                                            ({{ $bookingOrder->number_of_adults }} {{ __('Adults') }},
                                            {{ $bookingOrder->number_of_children }} {{ __('Children') }},
                                            {{ $bookingOrder->number_of_infants }} {{ __('Infants') }},
                                            {{ $bookingOrder->number_of_pets }} {{ __('Pets') }})
                                        </span>
                                    </span>
                                </div>

                                {{-- Primary Guest --}}
                                <div class="p-4 flex items-center border-b border-gray-100">
                                    @if($order->user->photo)
                                        <img src="{{ $order->user->photo }}" class="w-10 h-10 rounded-full object-cover"
                                            alt="{{ $order->user->name }}">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center font-bold text-primary-600">
                                            {{ substr($order->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="ml-3">
                                        <p class="text-sm font-bold text-gray-900">{{ $order->user->name }} <span
                                                class="ml-2 px-2 py-0.5 rounded text-[10px] bg-primary-100 text-primary-700 uppercase font-bold">{{ __('Primary') }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $order->user->email }} •
                                            {{ $order->user->phone }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Additional Guests --}}
                                @if($bookingOrder->guests && $bookingOrder->guests->count() > 0)
                                    <div class="p-4 bg-gray-50/50">
                                        <p class="text-xs font-bold text-gray-400 uppercase mb-3">
                                            {{ __('Additional Guests') }}
                                        </p>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            @foreach($bookingOrder->guests as $guest)
                                                <div class="flex items-center p-2 bg-white rounded border border-gray-100">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500 mr-2">
                                                        @if(!empty($guest->full_name))
                                                            {{ substr($guest->full_name, 0, 1) }}
                                                        @else
                                                            <x-heroicon-s-user class="w-4 h-4 text-gray-400" />
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">
                                                            {{ !empty($guest->full_name) ? $guest->full_name : ucfirst($guest->guest_type) }}
                                                        </p>
                                                        @if(!empty($guest->full_name))
                                                            <p class="text-xs text-gray-500">{{ ucfirst($guest->guest_type) }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>

                        {{-- Payment Details --}}
                        <section>
                            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Payment Details') }}</h3>
                            <div class="bg-white border border-gray-200 rounded-xl p-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between text-gray-600 text-sm">
                                        <span>{{ currencyFormat($bookingOrder->base_price) }} x
                                            {{ $bookingOrder->number_of_nights }} {{ __('nights') }}</span>
                                        <span>{{ currencyFormat($bookingOrder->total_nights_cost) }}</span>
                                    </div>
                                    @foreach($bookingOrder->fees as $fee)
                                        <div class="flex justify-between text-gray-600 text-sm">
                                            <span>{{ $fee->name }}</span>
                                            <span>{{ currencyFormat($fee->calculated_amount) }}</span>
                                        </div>
                                    @endforeach
                                    @if($order->discount > 0)
                                        <div class="flex justify-between text-green-600 text-sm font-medium">
                                            <span>{{ __('Discount') }}</span>
                                            <span>-{{ currencyFormat($order->discount) }}</span>
                                        </div>
                                    @endif
                                    @if($order->tax > 0)
                                        <div class="flex justify-between text-gray-600 text-sm">
                                            <span>{{ __('Tax') }}</span>
                                            <span>{{ currencyFormat($order->tax) }}</span>
                                        </div>
                                    @endif
                                    <div class="border-t border-gray-100 my-4"></div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-lg text-gray-900">{{ __('Total') }}</span>
                                        <span
                                            class="font-bold text-xl text-primary-600">{{ currencyFormat($order->total) }}</span>
                                    </div>

                                    @if($order->payment_method)
                                        <div class="flex justify-between items-center text-sm mt-4">
                                            <span class="text-gray-500">{{ __('Payment Method') }}</span>
                                            <div class="flex items-center">
                                                <img src="{{ $order->payment_method->photo }}"
                                                    class="w-6 h-6 mr-2 object-contain"
                                                    alt="{{ $order->payment_method->name }}">
                                                <span
                                                    class="font-medium text-gray-900">{{ $order->payment_method->name }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div
                                        class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center text-sm">
                                        <span class="text-gray-500">{{ __('Payment Status') }}</span>
                                        <span class="font-bold px-2 py-1 rounded"
                                            style="background-color: {{ $order->payment_status_color }}20; color: {{ $order->payment_status_color }}">
                                            {{ ucfirst(__($order->payment_status)) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    {{-- RIGHT COLUMN: Property & Actions --}}
                    <div class="space-y-8">

                        {{-- Actions Card --}}
                        @can('manage-bookings')
                            <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6">
                                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">
                                    {{ __('Actions') }}
                                </h3>

                                {{-- Payment Review Section --}}
                                @if($order->payment_status === 'review')
                                    <div class="mb-6 pb-6 border-b border-gray-100 bg-yellow-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-sm font-bold text-yellow-800 uppercase">{{ __('Review Payment') }}
                                            </h4>
                                            <span
                                                class="bg-yellow-200 text-yellow-800 text-xs px-2 py-1 rounded-full font-bold">{{ __('Action Required') }}</span>
                                        </div>
                                        <p class="text-xs text-yellow-700 mb-4">
                                            {{ __('This payment has been flagged for review. Please verify and take action.') }}
                                        </p>

                                        @if($order->payment)
                                            <div class="mb-4 space-y-3">
                                                <div>
                                                    <span class="text-xs font-bold text-gray-500 uppercase block">
                                                        {{ __('Transaction Code') }}
                                                    </span>
                                                    <span class="text-sm font-medium text-gray-900 break-all">
                                                        {{ $order->payment->ref }}
                                                    </span>
                                                </div>
                                                @if($order->payment->photo)
                                                    <div>
                                                        <span class="text-xs font-bold text-gray-500 uppercase block mb-1">
                                                            {{ __('Transaction Image') }}
                                                        </span>
                                                        <a href="{{ $order->payment->photo }}" target="_blank"
                                                            class="block relative w-full h-32 rounded-lg overflow-hidden border border-gray-200 bg-gray-100 hover:opacity-90 transition">
                                                            <img src="{{ $order->payment->photo }}" class="w-full h-full object-cover">
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-2 gap-3">
                                            <button wire:click="acceptPayment" wire:loading.attr="disabled"
                                                class="flex items-center justify-center w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-200">
                                                <x-heroicon-s-check class="w-4 h-4 mr-1" />
                                                {{ __('Accept') }}
                                            </button>
                                            <button wire:click="rejectPayment" wire:loading.attr="disabled"
                                                wire:confirm="{{ __('Are you sure you want to reject this payment? This will fail the order.') }}"
                                                class="flex items-center justify-center w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-200">
                                                <x-heroicon-s-x class="w-4 h-4 mr-1" />
                                                {{ __('Reject') }}
                                            </button>
                                        </div>
                                    </div>
                                @endif

                                {{-- Check-in/Check-out Verification --}}
                                @php
                                    $isCheckin = $order->status == \App\Models\BookingOrder::CHECKIN;
                                    $isAwaitingApproval = $order->status == \App\Models\BookingOrder::AWAITING_HOST_APPROVAL;
                                    $isPaid = $order->payment_status == 'successful';
                                    $isPendingPayment = $order->payment_status == 'pending';
                                    $orderIsStillAlive = !in_array($order->status, ['cancelled', 'completed', 'archived', 'delivered', 'failed']);
                                @endphp
                                @if($isPaid && ($isCheckin || $isAwaitingApproval))
                                    <div class="mb-6 pb-6 border-b border-gray-100">
                                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">
                                            {{ $isCheckin ? __('Checkout Verification Code') : __('Check-in Verification Code') }}
                                        </label>
                                        <div class="space-y-2">
                                            <x-input wire:model.defer="verificationCode" placeholder="{{ __('Enter code') }}" />
                                            <x-buttons.primary wireClick="verifyPickupCode" wire:loading.attr="disabled">
                                                {{ __('Verify') }}
                                            </x-buttons.primary>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-2">
                                            {{ __('Ask the guest for the verification code from their app.') }}
                                        </p>

                                        <x-form-errors />
                                    </div>
                                @endif

                                @if($isPendingPayment && $orderIsStillAlive)
                                    <x-input title="{{ __('Note to guest (Optional)') }}" name="note" class="text-sm mb-4" />
                                    <div class="space-y-3">
                                        <button wire:click="approveBooking"
                                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                                            {{ __('Approve Booking') }}
                                        </button>
                                        <button wire:click="rejectBooking"
                                            class="w-full bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-3 px-4 rounded-lg transition duration-200">
                                            {{ __('Reject Booking') }}
                                        </button>
                                    </div>
                                @elseif(!$isCheckin && !$isAwaitingApproval)
                                    <div class="space-y-4">
                                        @if($orderIsStillAlive)
                                            <div>
                                                <label
                                                    class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ __('Update Status') }}</label>
                                                <div class="flex gap-2">
                                                    <select wire:model="status"
                                                        class="block w-full text-sm rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                                                        @foreach($order_status as $s)
                                                            <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button wire:click="updateStatus"
                                                        class="bg-primary-600 text-white px-3 rounded-lg hover:bg-primary-700">
                                                        <x-heroicon-s-check class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        @endif

                                        @if($order->can_cancel && $order->status != "cancelled")
                                            <button wire:click="cancelBooking"
                                                class="w-full flex items-center justify-center px-4 py-2 border border-rose-300 text-rose-600 hover:bg-rose-50 rounded-lg font-bold transition">
                                                <x-heroicon-o-x-circle class="w-5 h-5 mr-2" />
                                                {{ __('Cancel Booking') }}
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endcan

                        {{-- Property Card --}}
                        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            @if($bookingOrder->property->photos && count($bookingOrder->property->photos) > 0)
                                <div class="h-40 w-full relative">
                                    <img src="{{ $bookingOrder->property->photos[0] }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                                    <div class="absolute bottom-3 left-3 text-white">
                                        <p class="font-bold text-lg leading-tight">{{ $bookingOrder->property->name }}</p>
                                    </div>
                                </div>
                            @endif
                            <div class="p-5">
                                <div class="flex items-start mb-4">
                                    <x-heroicon-s-map class="w-5 h-5 text-gray-400 mr-2 flex-shrink-0" />
                                    <p class="text-sm text-gray-600">
                                        {{ $bookingOrder->property->address ?? '' }}<br>
                                        {{ $bookingOrder->property->city ?? '' }},
                                        {{ $bookingOrder->property->country ?? '' }}
                                    </p>
                                </div>

                                {{-- Host Mini --}}
                                <div class="flex items-center pt-4 border-t border-gray-100">
                                    <img src="{{ $order->vendor->logo ?? '' }}" class="w-8 h-8 rounded-full bg-gray-200"
                                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($order->vendor->name) }}'">
                                    <div class="ml-3">
                                        <p class="text-xs text-gray-500 uppercase tracking-widest">{{ __('Hosted by') }}
                                        </p>
                                        <p class="text-sm font-bold text-gray-900">{{ $order->vendor->name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>