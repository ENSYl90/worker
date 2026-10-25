@section('title', __('Subscriptions'))
<div>
    @php
        $isForService = \Auth::user()->vendor->vendor_type->is_service ?? false;
        $isForProduct = \Auth::user()->vendor->vendor_type->is_product ?? false;
        $isForDelivery = \Auth::user()->vendor->vendor_type->is_parcel ?? false;
    @endphp

    <x-baseview title="{{ __('Subscriptions Plan') }}">
        <div class="mt-8 mb-12 text-center">
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight sm:text-5xl mb-4">
                {{ __('Select the perfect plan for your business') }}
            </h1>
            <p class="max-w-2xl mx-auto text-xl text-gray-500">
                {{ __('Scale your operations with our flexible subscription models tailored for your growth.') }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 max-w-7xl mx-auto px-4">
            @foreach ($subscriptions as $subscription)
                @php
                    $isPopular = $subscription->id == $popularSubscriptionId;
                    $btnClass = 'bg-gray-900 text-white hover:bg-black shadow-md hover:shadow-lg';
                    if ($isPopular) {
                        $btnClass = 'bg-primary-600 text-theme hover:bg-primary-700 shadow-md hover:shadow-lg';
                    }
                @endphp
                <div
                    class="relative flex flex-col p-8 bg-white border border-gray-200 rounded-3xl shadow-sm transition-all duration-300 hover:shadow-2xl hover:-translate-y-2 group {{ $isPopular ? 'ring-2 ring-primary-500' : '' }}">

                    @if ($isPopular)
                        <div class="absolute top-0 right-6 transform -translate-y-1/2">
                            <span
                                class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold tracking-widest uppercase bg-primary-500 text-white shadow-lg">
                                {{ __('Most Popular') }}
                            </span>
                        </div>
                    @endif

                    <div class="mb-8">
                        <h3 class="text-base font-bold text-gray-900 uppercase tracking-widest mb-2">
                            {{ $subscription->name }}
                        </h3>
                        <div class="flex items-baseline text-gray-900">
                            <span
                                class="text-3xl font-extrabold tracking-tight">{{ currencyFormat($subscription->amount) }}</span>
                            <span class="ml-1 text-base font-medium text-gray-500">/{{ $subscription->days }}
                                {{ __('days') }}</span>
                        </div>
                        <p class="mt-4 text-gray-500 text-sm italic">{{ __('Simple, transparent pricing') }}</p>
                    </div>

                    <ul role="list" class="flex-1 space-y-4 mb-8">
                        <li class="flex items-center">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-primary-50 flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">
                                <strong>{{ $subscription->qty }}</strong>
                                @if ($isForService)
                                    {{ __('Services') }}
                                @elseif($isForDelivery)
                                    {{ __('Package Types') }}
                                @else
                                    {{ __('Products') }}
                                @endif
                            </span>
                        </li>

                        <li class="flex items-center">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-primary-50 flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">{{ __('Full Platform Access') }}</span>
                        </li>

                        <li class="flex items-center">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-primary-50 flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">{{ __('Priority Support') }}</span>
                        </li>

                        <li class="flex items-center">
                            <div
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-primary-50 flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium">{{ __('Analytics Dashboard') }}</span>
                        </li>
                    </ul>

                    <button wire:click="subscriptionSelected('{{ $subscription->id }}')" class="mt-auto w-full py-4 px-6 rounded-2xl font-bold transition-all duration-200 flex items-center justify-center group-hover:scale-[1.02] active:scale-95
                                                                                {{ $btnClass }}">
                        {{ __('Choose This Plan') }}
                        <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Trust badges or info section -->
        <div class="mt-20 border-t border-gray-100 pt-16 mb-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 text-center max-w-5xl mx-auto">
                <div>
                    <div class="mx-auto w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-2">{{ __('Secure Payments') }}</h4>
                    <p class="text-gray-500 text-sm">
                        {{ __('Your transactions are encrypted and processed by industry leaders.') }}
                    </p>
                </div>
                <div>
                    <div class="mx-auto w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-2">{{ __('Instant Activation') }}</h4>
                    <p class="text-gray-500 text-sm">
                        {{ __('Get immediate access to all platform features upon successful payment.') }}
                    </p>
                </div>
                <div>
                    <div class="mx-auto w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-2">{{ __('Flexible Renewal') }}</h4>
                    <p class="text-gray-500 text-sm">
                        {{ __('No strict lock-ins. Renew or upgrade your plan at any time easily.') }}
                    </p>
                </div>
            </div>
        </div>
    </x-baseview>

    {{-- payment selector model --}}
    <div x-data="{ open: @entangle('showCreate') }">
        <x-modal-lg :clickAway="false" title="{{ __('Subscription Payment') }}">

            <div class="p-4">
                <div class="mb-8 text-center">
                    <div class="inline-flex items-center px-4 py-2 bg-primary-50 rounded-2xl mb-4">
                        <span class="text-primary-700 font-bold text-lg">
                            {{ $selectedSubscription->name ?? '' }} -
                            {{ currencyFormat($selectedSubscription->amount ?? 0) }}
                        </span>
                    </div>
                    <h2 class="text-2xl font-black text-gray-900">{{ __('Complete Your Subscription') }}</h2>
                    <p class="text-gray-500 mt-2">{{ __('Select your payment method below') }}</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($paymentMethods as $paymentMethod)
                        <button wire:click='initPayment({{ $paymentMethod->id }})'
                            class="relative group bg-white border-2 border-gray-100 rounded-3xl p-6 transition-all duration-200 hover:border-primary-500 hover:shadow-xl text-left overflow-hidden">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="flex-shrink-0 w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center overflow-hidden border border-gray-100 group-hover:bg-primary-50 transition-colors">
                                    <img src="{{ $paymentMethod->photo }}" alt="{{ $paymentMethod->name }}"
                                        class="w-full h-full object-contain p-2" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-lg">{{ $paymentMethod->name }}</h3>
                                    <p class="text-xs text-gray-400 group-hover:text-primary-500 font-semibold">
                                        {{ __('Secure Checkout') }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="absolute top-4 right-4 text-gray-200 group-hover:text-primary-500 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </div>
                        </button>
                    @endforeach
                </div>

                <div
                    class="mt-10 flex items-center justify-center space-x-2 text-gray-400 text-xs font-semibold uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ __('Encrypted & Secure') }}</span>
                </div>
            </div>

        </x-modal-lg>
    </div>

    {{-- payment processing model --}}
    <div x-data="{ open: @entangle('showEdit') }">
        <x-modal :withForm="false">
            <div class="p-6">
                @if (($selectedPaymentMethod->slug ?? '') == 'offline')
                    @include('livewire.payment.offline.subscription')
                @elseif(($selectedPaymentMethod->slug ?? '') == 'paypal')
                    <div id="paypal-button-container" class="py-12 min-h-[200px] flex items-center justify-center"></div>
                @endif
                {{-- paytm --}}
                @include('livewire.payment.gateways.paytm')
                {{-- payU --}}
                @include('livewire.payment.gateways.payu')

                @if (($selectedPaymentMethod->slug ?? '') != 'offline')
                    <div class="mt-8 text-center">
                        <div class="animate-spin inline-block w-8 h-8 border-[3px] border-current border-t-transparent text-primary-600 rounded-full mb-4"
                            role="status" aria-label="loading">
                            <span class="sr-only">{{ __("Loading...") }}</span>
                        </div>
                        <p class="text-sm font-bold text-gray-900">{{ __('Processing your payment...') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Please do not refresh or close this window') }}</p>
                    </div>
                @endif
            </div>
        </x-modal>
    </div>

</div>

@push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script
        src="https://www.paypal.com/sdk/js?client-id={{ $paypalMethod->public_key ?? '' }}&currency={{ setting('currencyCode', 'USD') }}&intent=capture">
        </script>
    <script src="{{ asset('js/subscription.js') }}"></script>
@endpush