<div>
    @hasanyrole('manager')
        @php
            $isSubscribeRoute = \Route::currentRouteName() === 'my.subscribe';
            $isDashboardRoute = \Route::currentRouteName() === 'dashboard';
            $showFullModal = $subExpired && !$isSubscribeRoute && !$isDashboardRoute;
            $showBanner = $subExpired && ($isDashboardRoute || $isSubscribeRoute);
        @endphp

        @if ($showFullModal)
            <!-- Full Page Modal -->
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 backdrop-blur-sm">
                <div class="relative max-w-2xl mx-4 overflow-hidden bg-white rounded-2xl shadow-2xl">
                    <!-- Modal Header with Gradient -->
                    <div class="relative bg-gradient-to-r from-red-500 to-red-600 px-8 py-6">
                        <div class="absolute inset-0 bg-primary-500 opacity-10"></div>
                        <div class="relative flex items-center justify-center space-x-4 text-theme">
                            <!-- Warning Icon -->
                            <div class="flex-shrink-0">
                                <svg class="w-16 h-16 text-theme animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>

                            <!-- Header Content -->
                            <div class="text-center">
                                <h2 class="text-3xl font-bold mb-2">{{ __('Subscription Required') }}</h2>
                                <p class="text-red-100 text-lg">{{ __('Access Restricted') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-8 py-8 text-center bg-white">
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">{{ __('Your subscription has expired') }}
                            </h3>
                            <p class="text-gray-600 leading-relaxed mb-6">
                                {{ __('To continue using all features and accessing this page, please renew your subscription. You will be redirected to complete your subscription.') }}
                            </p>
                        </div>

                        <!-- Features List -->
                        <div class="mb-8 text-left bg-gray-50 rounded-lg p-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">{{ __('What you\'re missing:') }}</h4>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Full access to all features') }}
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Unlimited data access') }}
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Priority support') }}
                                </li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('my.subscribe') }}"
                                class="inline-flex items-center justify-center px-8 py-4 bg-primary-600 text-white font-bold rounded-xl shadow-lg hover:bg-primary-700 transform hover:scale-105 transition-all duration-200 ease-in-out">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                {{ __('Subscribe Now') }}
                            </a>

                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center justify-center px-8 py-4 bg-gray-200 text-gray-700 font-semibold rounded-xl shadow-md hover:bg-gray-300 transition-all duration-200 ease-in-out">
                                {{ __('Back to Dashboard') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @elseif ($showBanner)
            <!-- Regular Banner for Dashboard/Subscribe Pages -->
            <div class="relative overflow-hidden bg-gradient-to-r from-red-500 to-red-600 shadow-lg">
                <div class="absolute inset-0 bg-black opacity-10"></div>
                <div class="relative px-6 py-4">
                    <div class="flex items-center justify-center space-x-4 text-white">
                        <!-- Warning Icon -->
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>

                        <!-- Message Content -->
                        <div class="flex-1 text-center">
                            <h3 class="text-lg font-semibold mb-1">{{ __('Subscription Expired') }}</h3>
                            <p class="text-red-100">
                                {{ __('Your subscription has expired. Please renew to continue accessing all features.') }}
                            </p>
                        </div>

                        <!-- Subscribe Button -->
                        <div class="flex-shrink-0">
                            <a href="{{ route('my.subscribe') }}"
                                class="inline-flex items-center px-6 py-3 bg-white text-red-600 font-semibold rounded-lg shadow-md hover:bg-gray-50 hover:shadow-lg transform hover:scale-105 transition-all duration-200 ease-in-out">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                {{ __('Subscribe Now') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Animated border bottom -->
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-red-300 to-red-200 animate-pulse">
                </div>
            </div>
        @endif
    @endhasanyrole
</div>
