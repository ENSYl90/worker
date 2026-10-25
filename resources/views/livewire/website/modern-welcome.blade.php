@extends('layouts.guest')
@php
    $appName = setting('websiteName', env('APP_NAME'));
    $pageTitle = $appName . ' - ' . setting('website.modern.websiteHeaderTitle', '');
@endphp
@section('title', $pageTitle ?? $appName)

@section('content')
    @php
        $colors = getAppColorShades();

        $modules = \App\Services\WebsiteModuleService::getModules();
        $foodModule = $modules['food'];
        $groceryModule = $modules['grocery'];
        $taxiModule = $modules['taxi'];
        $parcelModule = $modules['parcel'];
        $serviceModule = $modules['service'];
        $pharmacyModule = $modules['pharmacy'];
    @endphp
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, {{ $colors['shades'][500] }} 0%, {{ $colors['shades'][900] }} 100%);
        }

        .service-card {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }

        .floating-animation:nth-child(2) {
            animation-delay: -2s;
        }

        .floating-animation:nth-child(3) {
            animation-delay: -4s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }

        @keyframes pulse-glow {
            from {
                box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
            }

            to {
                box-shadow: 0 0 30px rgba(102, 126, 234, 0.6);
            }
        }

        .text-gradient {
            background: linear-gradient(135deg, {{ $colors['shades'][500] }} 0%, {{ $colors['shades'][900] }} 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .from-primary-600 {
            --tw-gradient-from: {{ $colors['shades'][600] }};
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(255, 255, 255, 0));
        }

        .to-primary-600 {
            --tw-gradient-to: {{ $colors['shades'][600] }};
        }
    </style>

    <!-- Navigation -->
    <nav class="fixed top-0 z-50 w-full border-b border-gray-200 bg-white/80 backdrop-blur-md">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="text-2xl font-bold text-gradient">{{ $appName }}</div>
                </div>
                <div class="items-center hidden space-x-8 md:flex">
                    <a href="#services"
                        class="text-gray-700 transition-colors hover:text-primary-600">{{ __('Services') }}</a>
                    <a href="#how-it-works"
                        class="text-gray-700 transition-colors hover:text-primary-600">{{ __('How It Works') }}</a>
                    <a href="#download"
                        class="text-gray-700 transition-colors hover:text-primary-600">{{ __('Download') }}</a>

                        @php
                            $showAuthButton = (bool) setting('website.modern.enable_backend_button', true);
                        @endphp
                    @if ($showAuthButton)
                        <a href="{{ route('login') }}"
                            class="px-6 py-2 text-white transition-all rounded-full bg-primary-600 hover:bg-primary-700 pulse-glow">
                            {{ __('Admin/Store Login') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative flex items-center min-h-screen pt-16 overflow-hidden gradient-bg">
        <div class="absolute inset-0 bg-black/20"></div>

        <!-- Floating Elements -->
        <div class="absolute w-20 h-20 rounded-full top-20 left-10 bg-white/20 floating-animation"></div>
        <div class="absolute w-16 h-16 rounded-full top-40 right-20 bg-white/15 floating-animation"></div>
        <div class="absolute w-12 h-12 rounded-full bottom-32 left-1/4 bg-white/25 floating-animation"></div>

        <div class="relative z-10 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid items-center grid-cols-1 gap-12 lg:grid-cols-2">
                <!-- Left Content -->
                <div class="text-center lg:text-left">
                    <h1 class="mb-6 text-5xl font-bold leading-tight text-white md:text-7xl">
                        {!! setting('website.modern.websiteHeaderTitle', '') !!}
                    </h1>
                    <p class="mb-8 text-xl md:text-2xl text-white/90">
                        {!! nl2br(setting('website.modern.websiteHeaderSubtitle', '')) !!}
                    </p>
                    <div class="flex flex-col justify-center gap-4 sm:flex-row lg:justify-start">
                        <a href="#download"
                            class="px-8 py-4 text-lg font-semibold transition-all transform bg-white rounded-full shadow-lg text-primary-600 hover:bg-gray-100 hover:scale-105">
                            📱 {{ __('Download App') }}
                        </a>
                    </div>
                </div>

                <!-- Right Image Showcase -->
                <div class="relative flex justify-center lg:justify-end">
                    <div class="relative">
                        <!-- Main Phone Mockup -->
                        <div class="relative z-10 transition-transform duration-700 transform rotate-3 hover:rotate-0">
                            <div class="p-2 bg-gray-900 shadow-2xl rounded-3xl">
                                <div class="overflow-hidden bg-white rounded-2xl" style="width: 280px; height: 580px;">
                                    <!-- Phone Screen Content -->
                                    <div class="flex flex-col h-full p-4 bg-gradient-to-br from-primary-600 to-primary-600">
                                        <!-- Status Bar -->
                                        <div class="flex items-center justify-between mb-4 text-sm text-white">
                                            <span>9:41</span>
                                            <span>●●●●●</span>
                                            <span>🔋 100%</span>
                                        </div>

                                        <!-- App Header -->
                                        <div class="mb-6 text-center">
                                            <h2 class="text-2xl font-bold text-white">{{ $appName }}</h2>
                                            <p class="text-sm text-white/80">
                                                {{ cached_setting('website.modern.websitePhoneSubtitle', __('What do you need today?')) }}
                                            </p>
                                        </div>

                                        <!-- Service Grid -->
                                        <div class="grid grid-cols-2 gap-3 mb-6 lg:grid-cols-3">
                                            @if ($foodModule)
                                                <div class="p-4 text-center bg-white/20 backdrop-blur-sm rounded-xl">
                                                    <div class="mb-2 text-2xl">🍕</div>
                                                    <div class="text-sm font-medium text-white">{{ __('Food') }}</div>
                                                </div>
                                            @endif
                                            @if ($groceryModule)
                                                <div class="p-4 text-center bg-white/20 backdrop-blur-sm rounded-xl">
                                                    <div class="mb-2 text-2xl">🥬</div>
                                                    <div class="text-sm font-medium text-white">{{ __('Grocery') }}</div>
                                                </div>
                                            @endif
                                            @if ($taxiModule)
                                                <div class="p-4 text-center bg-white/20 backdrop-blur-sm rounded-xl">
                                                    <div class="mb-2 text-2xl">🚗</div>
                                                    <div class="text-sm font-medium text-white">{{ __('Taxi') }}</div>
                                                </div>
                                            @endif
                                            @if ($parcelModule)
                                                <div class="p-4 text-center bg-white/20 backdrop-blur-sm rounded-xl">
                                                    <div class="mb-2 text-2xl">📦</div>
                                                    <div class="text-sm font-medium text-white">{{ __('Parcel') }}</div>
                                                </div>
                                            @endif
                                            @if ($serviceModule)
                                                <div class="p-4 text-center bg-white/20 backdrop-blur-sm rounded-xl">
                                                    <div class="mb-2 text-2xl">🛠️</div>
                                                    <div class="text-sm font-medium text-white">{{ __('Service') }}</div>
                                                </div>
                                            @endif

                                        </div>

                                        <!-- Popular Section -->
                                        <div class="flex-1">
                                            <h3 class="mb-3 font-semibold text-white">{{ __('Popular') }}</h3>
                                            <div class="space-y-2">
                                                @if ($foodModule)
                                                    <div
                                                        class="flex items-center p-3 rounded-lg bg-white/20 backdrop-blur-sm">
                                                        <div
                                                            class="flex items-center justify-center w-8 h-8 mr-3 text-sm text-white bg-orange-500 rounded-full">
                                                            🍔
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-white">
                                                                {{ __('Burger Palace') }}
                                                            </div>
                                                            <div class="text-xs text-white/70">15 {{ __("min") }} • 4.8 ⭐</div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($groceryModule)
                                                    <div
                                                        class="flex items-center p-3 rounded-lg bg-white/20 backdrop-blur-sm">
                                                        <div
                                                            class="flex items-center justify-center w-8 h-8 mr-3 text-sm text-white bg-green-500 rounded-full">
                                                            🥗
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-white">
                                                                {{ __('Fresh Market') }}
                                                            </div>
                                                            <div class="text-xs text-white/70">8 min • 4.9 ⭐</div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if ($taxiModule)
                                                    <div
                                                        class="flex items-center p-3 rounded-lg bg-white/20 backdrop-blur-sm">
                                                        <div
                                                            class="flex items-center justify-center w-8 h-8 mr-3 text-sm text-white bg-blue-500 rounded-full">
                                                            🚙
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-white">
                                                                {{ __('Quick Ride') }}
                                                            </div>
                                                            <div class="text-xs text-white/70">2 {{ __("min pickup") }} • 4.7 ⭐</div>
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Service Icons -->
                        @if ($foodModule)
                            <div
                                class="absolute p-4 rounded-full -top-8 -left-8 bg-white/20 backdrop-blur-sm floating-animation">
                                <div class="text-3xl">🍕</div>
                            </div>
                        @endif
                        @if ($taxiModule)
                            <div
                                class="absolute p-4 rounded-full -bottom-8 -right-8 bg-white/20 backdrop-blur-sm floating-animation">
                                <div class="text-3xl">🚗</div>
                            </div>
                        @endif
                        @if ($parcelModule)
                            <div
                                class="absolute p-3 rounded-full top-1/2 -left-12 bg-white/20 backdrop-blur-sm floating-animation">
                                <div class="text-2xl">📦</div>
                            </div>
                        @endif
                        @if ($groceryModule)
                            <div
                                class="absolute p-3 rounded-full top-1/4 -right-12 bg-white/20 backdrop-blur-sm floating-animation">
                                <div class="text-2xl">🥬</div>
                            </div>
                        @endif
                        @if ($serviceModule)
                            <div
                                class="absolute p-3 rounded-full top-1/2 -right-12 bg-white/20 backdrop-blur-sm floating-animation">
                                <div class="text-2xl">🛠️</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-white">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-4xl font-bold text-gray-900 md:text-5xl">
                    {{ __('One App') }}, <span class="text-gradient">{{ __('Endless Possibilities') }}</span>
                </h2>
                <p class="max-w-3xl mx-auto text-xl text-gray-600">
                    {{ __('Why juggle multiple apps when :app_name brings everything together? Your local community, delivered.', ['app_name' => $appName]) }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Food Delivery -->
                @if ($foodModule)
                    <div
                        class="p-8 border border-orange-200 service-card bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl">
                        <div class="mb-4 text-5xl">🍕</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900">{{ $foodModule->name }}</h3>
                        <p class="mb-4 text-gray-600">
                            {{ $foodModule->description }}
                        </p>
                        <div class="flex items-center font-semibold text-orange-600">
                            <span>{{ __('Order Now') }}</span>
                            <span class="ml-2">→</span>
                        </div>
                    </div>
                @endif

                <!-- Grocery Delivery -->
                @if ($groceryModule)
                    <div
                        class="p-8 border border-green-200 service-card bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl">
                        <div class="mb-4 text-5xl">🥬</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900">{{ $groceryModule->name }}</h3>
                        <p class="mb-4 text-gray-600">
                            {{ $groceryModule->description }}
                        </p>
                        <div class="flex items-center font-semibold text-green-600">
                            <span>{{ __('Shop Now') }}</span>
                            <span class="ml-2">→</span>
                        </div>
                    </div>
                @endif

                <!-- Taxi Service -->
                @if ($taxiModule)
                    <div
                        class="p-8 border border-blue-200 service-card bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl">
                        <div class="mb-4 text-5xl">🚗</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900"> {{ $taxiModule->name }} </h3>
                        <p class="mb-4 text-gray-600">
                            {{ $taxiModule->description }}
                        </p>
                        <div class="flex items-center font-semibold text-blue-600">
                            <span> {{ __('Book Ride') }} </span>
                            <span class="ml-2">→</span>
                        </div>
                    </div>
                @endif

                <!-- Parcel Delivery -->
                @if ($parcelModule)
                    <div
                        class="p-8 border service-card bg-gradient-to-br from-primary-50 to-pink-50 rounded-2xl border-primary-200">
                        <div class="mb-4 text-5xl">📦</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900"> {{ $parcelModule->name }} </h3>
                        <p class="mb-4 text-gray-600">
                            {{ $parcelModule->description }}
                        </p>
                        <div class="flex items-center font-semibold text-primary-600">
                            <span> {{ __('Send Package') }} </span>
                            <span class="ml-2">→</span>
                        </div>
                    </div>
                @endif

                <!-- Pharmacy -->
                @if ($pharmacyModule)
                    <div
                        class="p-8 border border-teal-200 service-card bg-gradient-to-br from-teal-50 to-cyan-50 rounded-2xl">
                        <div class="mb-4 text-5xl">💊</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900"> {{ $pharmacyModule->name }} </h3>
                        <p class="mb-4 text-gray-600">{{ $pharmacyModule->description }} </p>
                        <div class="flex items-center font-semibold text-teal-600">
                            <span>{{ __('Order Meds') }} </span>
                            <span class="ml-2">→</span>
                        </div>
                    </div>
                @endif

                <!-- More Services -->
                @if ($serviceModule)
                    <div
                        class="p-8 border border-gray-200 service-card bg-gradient-to-br from-gray-50 to-slate-50 rounded-2xl">
                        <div class="mb-4 text-5xl">✨</div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900">{{ $serviceModule->name }} </h3>
                        <p class="mb-4 text-gray-600">
                            {{ $serviceModule->description }}
                        </p>
                        <div class="flex items-center font-semibold text-gray-600">
                            <span>{{ __('Explore All') }} </span>
                            <span class="ml-2">→</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-20 bg-gray-50">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-4xl font-bold text-gray-900 md:text-5xl">
                    {{ __('Simple as') }} <span class="text-gradient">1-2-3</span>
                </h2>
                <p class="text-xl text-gray-600">
                    {{ __('Getting what you need has never been easier') }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div class="text-center">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-primary-100">
                        <span class="text-3xl font-bold text-primary-600">1</span>
                    </div>
                    <h3 class="mb-4 text-2xl font-bold text-gray-900">{{ __('Choose Your Service') }} </h3>
                    <p class="text-gray-600">
                        {{ __('Select from food, groceries, taxi, delivery, or any other service you need.') }}
                    </p>
                </div>

                <div class="text-center">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-primary-100">
                        <span class="text-3xl font-bold text-primary-600">2</span>
                    </div>
                    <h3 class="mb-4 text-2xl font-bold text-gray-900">{{ __('Place Your Order') }} </h3>
                    <p class="text-gray-600">
                        {{ __('Browse local options, customize your order, and checkout securely.') }}
                    </p>
                </div>

                <div class="text-center">
                    <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 rounded-full bg-primary-100">
                        <span class="text-3xl font-bold text-primary-600">3</span>
                    </div>
                    <h3 class="mb-4 text-2xl font-bold text-gray-900">{{ __('Get It Delivered') }} </h3>
                    <p class="text-gray-600">{{ __('Track your order in real-time and receive it at your doorstep.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="download" class="relative py-20 overflow-hidden gradient-bg">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="relative z-10 max-w-4xl px-4 mx-auto text-center sm:px-6 lg:px-8">
            <h2 class="mb-6 text-4xl font-bold text-white md:text-5xl">
                {{ __('Ready to Experience') }} <span class="text-primary-400">{{ $appName }}</span>?
            </h2>
            <p class="max-w-2xl mx-auto mb-8 text-xl text-white/90">
                {{ __("Join thousands of satisfied customers who've made :app_name their go-to for everything local.", ['app_name' => $appName]) }}
            </p>
            <div class="flex flex-col justify-center gap-4 mb-8 sm:flex-row">
                <a href="https://play.google.com/store/apps/details?id={{ env('dynamic_link.android') }}"
                    class="flex items-center justify-center px-8 py-4 space-x-2 text-lg font-semibold text-white transition-all transform bg-black rounded-full shadow-lg hover:bg-gray-800 hover:scale-105">
                    <x-tabler-brand-android class="w-5 h-5" />
                    <p>{{ __('Get it on Google Play') }} </p>
                </a>
                <a href="https://apps.apple.com/app/{{ env('dynamic_link.ios_id') }}"
                    class="flex items-center justify-center px-8 py-4 space-x-2 text-lg font-semibold transition-all transform bg-white rounded-full shadow-lg text-primary-600 hover:bg-gray-100 hover:scale-105">
                    <x-tabler-brand-apple class="w-5 h-5" />
                    <p>{{ __('Download on App Store') }} </p>

                </a>
            </div>
            <p class="text-white/80">
                {{ __('Available on') }} Android {{ __('and') }} iOS •
                {{ __('Free Download') }}</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 text-white bg-gray-900">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <div>
                    <div class="mb-4 text-2xl font-bold">{{ $appName }}</div>
                    <p class="text-gray-400">
                        {!! nl2br(e(setting('website.modern.websiteFooterBrief', ''))) !!}
                    </p>
                </div>
                <div>
                    <h4 class="mb-4 font-semibold">{{ __("Services") }} </h4>
                    <ul class="space-y-2 text-gray-400">
                        @if ($foodModule)
                            <li>
                                <a href="#" class="transition-colors hover:text-white">
                                    {{ $foodModule->name }}
                                </a>
                            </li>
                        @endif
                        @if ($groceryModule)
                            <li>
                                <a href="#" class="transition-colors hover:text-white">
                                    {{ $groceryModule->name }}
                                </a>
                            </li>
                        @endif
                        @if ($taxiModule)
                            <li>
                                <a href="#" class="transition-colors hover:text-white">
                                    {{ $taxiModule->name }}
                                </a>
                            </li>
                        @endif
                        @if ($parcelModule)
                            <li>
                                <a href="#" class="transition-colors hover:text-white">
                                    {{ $parcelModule->name }}
                                </a>
                            </li>
                        @endif
                        @if ($serviceModule)
                            <li>
                                <a href="#" class="transition-colors hover:text-white">
                                    {{ $serviceModule->name }}
                                </a>
                            </li>
                        @endif
                        @if ($pharmacyModule)
                            <li>
                                <a href="#" class="transition-colors hover:text-white">
                                    {{ $pharmacyModule->name }}
                                </a>
                            </li>
                        @endif

                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 font-semibold">{{ __("Company") }} </h4>
                    <ul class="space-y-2 text-gray-400">
                        {{-- <li><a href="#" class="transition-colors hover:text-white">{{ __("About") }} </a></li> --}}
                        {{-- <li><a href="#" class="transition-colors hover:text-white">{{ __("Careers") }} </a></li> --}}
                        {{-- <li><a href="#" class="transition-colors hover:text-white">{{ __("Partner") }} </a></li> --}}
                        <li>
                            <a href="{{ route('register.driver') }}" class="transition-colors hover:text-white">
                                {{ __('Driver Sign-Up') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register.vendor') }}" class="transition-colors hover:text-white">
                                {{ __('Store/Vendor Sign-Up') }}
                            </a>
                        </li>
                        <hr class="w-2/12 my-2 text-theme" />
                        {{--  --}}
                        <li>
                            <a href="{{ route('contact') }}" class="transition-colors hover:text-white">
                                {{ __('Support') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('terms') }}" class="transition-colors hover:text-white">
                                {{ __('Terms of Service') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('privacy') }}" class="transition-colors hover:text-white">
                                {{ __('Privacy Policy') }}
                            </a>
                        </li>

                    </ul>
                </div>
                <div>
                    <h4 class="mb-4 font-semibold">{{ __("Connect") }} </h4>
                    <ul class="space-y-2 text-gray-400">
                        <li>
                            <a href="{{ setting('website.modern.social.fbLink', '') }}"
                                class="transition-colors hover:text-white">Facebook
                            </a>
                        </li>
                        <li>
                            <a href="{{ setting('website.modern.social.twLink', '') }}"
                                class="transition-colors hover:text-white">Twitter
                            </a>
                        </li>
                        <li>
                            <a href="{{ setting('website.modern.social.igLink', '') }}"
                                class="transition-colors hover:text-white">Instagram
                            </a>
                        </li>
                        <li>
                            <a href="{{ setting('social.liLink', '') }}"
                                class="transition-colors hover:text-white">LinkedIn
                            </a>
                        </li>
                        <li>
                            <a href="{{ setting('website.modern.social.yuLink', '') }}"
                                class="transition-colors hover:text-white">
                                Youtube
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 mt-8 text-center text-gray-400 border-t border-gray-800">
                <p>
                    &copy; {{ date('Y') }} {{ $appName }}.
                    {{ __('All rights reserved') }}. | <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a> |
                    <a href="{{ route('terms') }}">{{ __('Terms of Service') }}</a>
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('nav');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            } else {
                navbar.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
            }
        });

        // Service card hover effects
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
@endsection
