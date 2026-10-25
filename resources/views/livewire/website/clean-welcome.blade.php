@extends('layouts.guest')
@php
    $appName = setting('websiteName', env('APP_NAME'));
    $pageTitle = $appName . ' - ' . setting('website.modern.websiteHeaderTitle', 'The Super App');
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
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f3f4f6; /* zinc-100 */
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        :root {
            --primary: {{ $colors['shades'][500] ?? '#10b981' }};
            --primary-hover: {{ $colors['shades'][600] ?? '#059669' }};
            --primary-light: {{ $colors['shades'][100] ?? '#d1fae5' }};
            --dark: #020617;
            --food: #ef4444;       /* red */
            --grocery: #10b981;    /* emerald */
            --taxi: #3b82f6;       /* blue */
            --parcel: #f59e0b;     /* amber */
            --pharmacy: #14b8a6;   /* teal */
            --service: #8b5cf6;    /* violet */
        }

        .text-super-primary { color: var(--primary); }
        .bg-super-primary { background-color: var(--primary); }
        .bg-super-primary:hover { background-color: var(--primary-hover); }

        /* Navigation */
        .super-nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        /* Buttons */
        .pill-btn {
            border-radius: 9999px;
            padding: 0.875rem 1.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .pill-btn-primary {
            background-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 4px 14px 0 rgba(0, 0, 0, 0.1);
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .pill-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            background-color: var(--primary-hover);
            color: #ffffff;
        }

        .pill-btn-dark {
            background-color: var(--dark);
            color: white;
        }
        .pill-btn-dark:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(0,0,0,0.5);
            color: white;
        }

        /* Hero Typography */
        .hero-title {
            font-size: clamp(3rem, 6vw, 5.5rem);
            line-height: 1.05;
            letter-spacing: -0.03em;
            color: var(--dark);
        }

        /* Bento Grid System */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 1.5rem;
        }

        .bento-card {
            background: white;
            border-radius: 2rem;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
        }

        .bento-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.08);
            border-color: transparent;
        }

        .col-span-12 { grid-column: span 12; }
        @media (min-width: 768px) {
            .md\:col-span-8 { grid-column: span 8; }
            .md\:col-span-6 { grid-column: span 6; }
            .md\:col-span-4 { grid-column: span 4; }
        }
        @media (min-width: 1024px) {
            .lg\:col-span-4 { grid-column: span 4; }
            .lg\:col-span-8 { grid-column: span 8; }
            .lg\:col-span-6 { grid-column: span 6; }
            .lg\:col-span-3 { grid-column: span 3; }
        }

        /* Service Cards styling */
        .service-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 10;
            background: white;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        }

        .card-content {
            position: relative;
            z-index: 10;
        }

        /* Blobs for cards */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            z-index: 0;
            opacity: 0.15;
            transition: all 0.5s ease;
        }
        .bento-card:hover .blob {
            transform: scale(1.3);
            opacity: 0.25;
        }

        .blob-food { background: var(--food); width: 250px; height: 250px; bottom: -50px; right: -50px; }
        .blob-grocery { background: var(--grocery); width: 250px; height: 250px; bottom: -50px; right: -50px; }
        .blob-taxi { background: var(--taxi); width: 250px; height: 250px; bottom: -50px; right: -50px; }
        .blob-parcel { background: var(--parcel); width: 250px; height: 250px; top: -50px; right: -50px; }
        .blob-pharmacy { background: var(--pharmacy); width: 200px; height: 200px; bottom: -20px; right: -20px; }
        .blob-service { background: var(--service); width: 200px; height: 200px; top: -20px; right: -20px; }

        /* The Phone View in Hero */
        .super-phone-wrapper {
            position: relative;
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
            perspective: 1000px;
        }
        .super-phone {
            width: 100%;
            height: 650px;
            background: #ffffff;
            border: 14px solid var(--dark);
            border-radius: 3rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            position: relative;
            overflow: hidden;
            transform: rotateY(-10deg) rotateX(10deg);
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .super-phone-wrapper:hover .super-phone {
            transform: rotateY(0deg) rotateX(0deg);
        }
        .phone-notch {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40%;
            height: 24px;
            background: var(--dark);
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
            z-index: 20;
        }

        .phone-ui-header {
            background: linear-gradient(to bottom, var(--dark), var(--primary-hover));
            padding: 3rem 1.5rem 1.5rem;
            color: white;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .phone-search {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 99px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            font-size: 0.875rem;
        }
        .phone-search input {
            background: transparent;
            color: white;
            border: none;
            outline: none;
            width: 100%;
        }
        .phone-search input::placeholder { color: rgba(255,255,255,0.7); }

        .app-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem 0.5rem;
            padding: 1.5rem;
        }
        .app-icon-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        .app-icon {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        /* Utility */
        .marquee-container {
            position: relative;
            display: flex;
            overflow: hidden;
            user-select: none;
            gap: 2rem;
            mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
            padding: 2rem 0;
        }
        .marquee-content {
            flex-shrink: 0;
            display: flex;
            gap: 2rem;
            min-width: 100%;
            animation: scroll 30s linear infinite;
        }
        @keyframes scroll {
            from { transform: translateX(0); }
            to { transform: translateX(calc(-100% - 2rem)); }
        }

        .pill-badge {
            background: var(--primary-light);
            color: var(--primary-hover);
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.875rem;
            display: inline-flex;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Trust indicator avatars */
        .avatar-initials {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            border: 2px solid #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
    </style>

    <!-- Navigation -->
    <nav class="fixed top-0 z-50 w-full super-nav py-4">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ appLogo() }}" alt="{{ $appName }}" class="w-10 h-10 object-contain" />
                <span class="text-2xl font-extrabold tracking-tight text-dark">{{ $appName }}</span>
            </div>
            
            <div class="hidden md:flex items-center space-x-10">
                <a href="#superapp" class="font-bold text-slate-600 hover:text-slate-900 transition-colors">{{ __('Super App') }}</a>
                <a href="#services" class="font-bold text-slate-600 hover:text-slate-900 transition-colors">{{ __('Services') }}</a>
                <a href="#business" class="font-bold text-slate-600 hover:text-slate-900 transition-colors">{{ __('For Business') }}</a>
            </div>

            <div class="flex items-center gap-4">
                @if (setting('website.modern.enable_backend_button', true))
                    <a href="{{ route('login') }}" class="font-bold text-slate-600 hover:text-slate-900 hidden sm:block">
                        {{ __('Log in') }}
                    </a>
                @endif
                <a href="#download" class="pill-btn pill-btn-dark py-2 px-5 text-sm">
                    {{ __('Download') }}
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="pt-40 pb-20 px-4 md:px-6 relative overflow-hidden" id="superapp">
        <!-- Abstract background pattern -->
        <div class="absolute inset-0 z-0 opacity-[0.03]" style="background-image: radial-gradient(var(--dark) 2px, transparent 2px); background-size: 30px 30px;"></div>

        <div class="max-w-7xl mx-auto relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Hero Text -->
            <div class="lg:col-span-7 space-y-8 text-center lg:text-left">
                <div class="inline-block">
                    <span class="pill-badge mb-4">{{ __('One App. Everything.') }}</span>
                </div>
                <h1 class="hero-title font-extrabold">
                    {!! setting('website.modern.websiteHeaderTitle', 'Solve your day<br>in one tap.') !!}
                </h1>
                <p class="text-xl text-slate-600 max-w-2xl mx-auto lg:mx-0 font-medium leading-relaxed">
                    {!! nl2br(setting('website.modern.websiteHeaderSubtitle', 'Order food, shop groceries, book a ride, and send packages—all from a single, powerful app.')) !!}
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start pt-4">
                    <a href="#download" class="pill-btn pill-btn-primary text-lg px-8 py-4">
                        {{ __('Get the App') }}
                    </a>
                    <a href="#services" class="pill-btn bg-white text-slate-800 border border-slate-200 hover:bg-slate-50 text-lg px-8 py-4 shadow-sm">
                        {{ __('Explore Services') }}
                    </a>
                </div>
                
                <!-- Trust indicators -->
                <div class="pt-8 flex items-center justify-center lg:justify-start gap-4 text-sm font-bold text-slate-500">
                    <div class="flex -space-x-2">
                        <div class="avatar-initials" style="background: #10b981;">JD</div>
                        <div class="avatar-initials" style="background: #3b82f6;">AM</div>
                        <div class="avatar-initials" style="background: #f59e0b;">SK</div>
                    </div>
                    <div>
                        <span class="text-slate-900 block star-rating">★★★★★</span>
                        <span>{{ __('Trusted by 10k+ locals') }}</span>
                    </div>
                </div>
            </div>

            <!-- Interactive App Mockup -->
            <div class="lg:col-span-5 relative hidden md:block">
                <!-- Decorative blobs behind phone -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-super-primary rounded-full mix-blend-multiply filter blur-[80px] opacity-20"></div>
                <div class="absolute top-1/2 left-1/4 -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-[80px] opacity-20"></div>
                
                <div class="super-phone-wrapper">
                    <div class="super-phone">
                        <div class="phone-notch"></div>
                        
                        <!-- Inside the Phone -->
                        <div class="h-full bg-[#f8f9fa] flex flex-col">
                            <div class="phone-ui-header">
                                <p class="text-white/80 text-sm font-medium mb-1">{{ __('Deliver to') }}</p>
                                <div class="flex items-center justify-between mb-6">
                                    <p class="font-bold text-lg">Current Location ▼</p>
                                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">👤</div>
                                </div>
                                <div class="phone-search">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <input type="text" placeholder="{{ __('Find food, rides, etc') }}">
                                </div>
                            </div>
                            
                            <!-- Phone App Grid -->
                            <div class="app-grid">
                                @if($foodModule)
                                <div class="app-icon-item">
                                    <div class="app-icon" style="background: {{ $foodModule->color ?? '#fee2e2' }}; color: #000;">🍔</div>
                                    <span class="text-[11px] font-bold text-slate-700">{{ $foodModule->name }}</span>
                                </div>
                                @endif
                                @if($taxiModule)
                                <div class="app-icon-item">
                                    <div class="app-icon" style="background: {{ $taxiModule->color ?? '#dbeafe' }}; color: #000;">🚕</div>
                                    <span class="text-[11px] font-bold text-slate-700">{{ $taxiModule->name }}</span>
                                </div>
                                @endif
                                @if($groceryModule)
                                <div class="app-icon-item">
                                    <div class="app-icon" style="background: {{ $groceryModule->color ?? '#d1fae5' }}; color: #000;">🛒</div>
                                    <span class="text-[11px] font-bold text-slate-700">{{ $groceryModule->name }}</span>
                                </div>
                                @endif
                                @if($parcelModule)
                                <div class="app-icon-item">
                                    <div class="app-icon" style="background: {{ $parcelModule->color ?? '#fef3c7' }}; color: #000;">📦</div>
                                    <span class="text-[11px] font-bold text-slate-700">{{ $parcelModule->name }}</span>
                                </div>
                                @endif
                                @if($pharmacyModule)
                                <div class="app-icon-item">
                                    <div class="app-icon" style="background: {{ $pharmacyModule->color ?? '#ccfbf1' }}; color: #000;">💊</div>
                                    <span class="text-[11px] font-bold text-slate-700">{{ $pharmacyModule->name }}</span>
                                </div>
                                @endif
                                @if($serviceModule)
                                <div class="app-icon-item">
                                    <div class="app-icon" style="background: {{ $serviceModule->color ?? '#ede9fe' }}; color: #000;">🔧</div>
                                    <span class="text-[11px] font-bold text-slate-700">{{ $serviceModule->name }}</span>
                                </div>
                                @endif
                            </div>
                            
                            <div class="px-4 mt-2 mb-2">
                                <h3 class="font-bold text-sm mb-3">{{ __('Offers near you') }}</h3>
                                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex items-center gap-4">
                                    <div class="w-16 h-16 bg-slate-100 rounded-xl flex items-center justify-center text-2xl">🍕</div>
                                    <div>
                                        <p class="font-bold text-sm">50% Off Pizza</p>
                                        <p class="text-xs text-slate-500 mt-1">Ends in 2 mins</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-auto bg-white border-t border-slate-200 p-4 pb-8 flex justify-between px-8">
                                <div class="text-super-primary"><svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg></div>
                                <div class="text-slate-300"><svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2m-7 9h-2V7h-2v5H6v2h2v5h2v-5h2v-2"/></svg></div>
                                <div class="text-slate-300"><svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-2 12H6v-2h12v2m0-3H6V9h12v2m0-3H6V6h12v2"/></svg></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </section>

    <!-- Services Bento Section -->
    <section id="services" class="py-24 px-4 md:px-6">
        <div class="max-w-7xl mx-auto">
            <div class="mb-16">
                <h2 class="text-4xl md:text-5xl font-extrabold text-dark tracking-tight mb-4">{{ __('Do more with') }} {{ $appName }}</h2>
                <p class="text-xl text-slate-500 font-medium max-w-2xl">{{ __('We bring the entire city right to your fingertips. Tap. Wait. Done.') }}</p>
            </div>

            <div class="bento-grid">
                
                @if($foodModule)
                <!-- Large Feature Box -->
                <div class="bento-card col-span-12 md:col-span-8 bg-white shadow-sm border-slate-100 group cursor-pointer h-[350px] relative overflow-hidden">
                    <div class="blob blob-food" style="background: {{ $foodModule->color ?? '#ef4444' }}; right: -50px; bottom: -50px; width: 450px; height: 450px; opacity: 0.4;"></div>
                    <div class="blob blob-food" style="background: {{ $foodModule->color ?? '#f97316' }}; right: 100px; top: -50px; width: 350px; height: 350px; opacity: 0.2;"></div>
                    
                    <div class="card-content flex flex-col justify-between h-full text-dark relative z-10">
                        <div>
                            <div class="service-icon-wrapper bg-red-50 mb-6 transition-transform group-hover:scale-110 group-hover:rotate-3 shadow-sm" style="color: {{ $foodModule->color ?? '#ef4444' }};">
                                🍔
                            </div>
                            <h3 class="text-3xl font-extrabold mb-2 text-slate-900">{{ $foodModule->name }}</h3>
                            <p class="text-slate-500 text-lg max-w-sm font-medium">{{ $foodModule->description ?? __('Cravings satisfied instantly. The best local restaurants delivered hot to your door.') }}</p>
                        </div>
                        <div class="mt-auto">
                            <span class="inline-flex items-center font-extrabold transition-transform group-hover:translate-x-2" style="color: {{ $foodModule->color ?? '#ef4444' }};">
                                {{ __('Order Food') }} →
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                @if($taxiModule)
                <!-- Side Feature Box -->
                <div class="bento-card col-span-12 md:col-span-4 group cursor-pointer h-[350px] shadow-sm bg-white">
                    <div class="blob blob-taxi" style="background: {{ $taxiModule->color ?? '#3b82f6' }} !important; opacity: 0.35; width: 300px; height: 300px; bottom: -40px; right: -40px;"></div>
                    <div class="card-content flex flex-col justify-between h-full">
                        <div>
                            <div class="service-icon-wrapper bg-blue-50 transition-transform group-hover:scale-110" style="color: {{ $taxiModule->color ?? '#3b82f6' }} !important;">
                                🚕
                            </div>
                            <h3 class="text-2xl font-extrabold mb-2 text-dark">{{ $taxiModule->name }}</h3>
                            <p class="text-slate-500 font-medium leading-relaxed">{{ $taxiModule->description ?? __('Safe, fast, and reliable rides whenever you need them.') }}</p>
                        </div>
                        <div class="mt-auto">
                            <span class="inline-flex items-center font-extrabold" style="color: {{ $taxiModule->color ?? '#3b82f6' }};">
                                {{ __('Book Ride') }} →
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                @if($groceryModule)
                <div class="bento-card col-span-12 lg:col-span-4 group cursor-pointer h-[300px] shadow-sm bg-white">
                    <div class="blob blob-grocery" style="background: {{ $groceryModule->color ?? '#10b981' }}; opacity: 0.3; width: 300px; height: 300px; bottom: -40px; right: -40px;"></div>
                    <div class="card-content flex flex-col justify-between h-full">
                        <div>
                            <div class="service-icon-wrapper bg-emerald-50 transition-transform group-hover:scale-110" style="color: {{ $groceryModule->color ?? '#10b981' }};">
                                🛒
                            </div>
                            <h3 class="text-2xl font-extrabold mb-2 text-dark">{{ $groceryModule->name }}</h3>
                            <p class="text-slate-500 font-medium leading-relaxed">{{ $groceryModule->description ?? __('Daily essentials and fresh groceries delivered fast.') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($parcelModule)
                <div class="bento-card col-span-12 lg:col-span-4 group cursor-pointer h-[300px] shadow-sm bg-white">
                    <div class="blob blob-parcel" style="background: {{ $parcelModule->color ?? '#f59e0b' }}; opacity: 0.3; width: 300px; height: 300px; top: -40px; right: -40px;"></div>
                    <div class="card-content flex flex-col justify-between h-full">
                        <div>
                            <div class="service-icon-wrapper bg-amber-50 transition-transform group-hover:scale-110" style="color: {{ $parcelModule->color ?? '#f59e0b' }};">
                                📦
                            </div>
                            <h3 class="text-2xl font-extrabold mb-2 text-dark">{{ $parcelModule->name }}</h3>
                            <p class="text-slate-500 font-medium leading-relaxed">{{ $parcelModule->description ?? __('Send packages anywhere in the city, tracked live.') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($pharmacyModule)
                <div class="bento-card col-span-12 lg:col-span-4 group cursor-pointer h-[300px] shadow-sm bg-white">
                    <div class="blob blob-pharmacy" style="background: {{ $pharmacyModule->color ?? '#14b8a6' }}; opacity: 0.3; width: 250px; height: 250px; bottom: -20px; right: -20px;"></div>
                    <div class="card-content flex flex-col justify-between h-full">
                        <div>
                            <div class="service-icon-wrapper bg-teal-50 transition-transform group-hover:scale-110" style="color: {{ $pharmacyModule->color ?? '#14b8a6' }};">
                                💊
                            </div>
                            <h3 class="text-2xl font-extrabold mb-2 text-dark">{{ $pharmacyModule->name }}</h3>
                            <p class="text-slate-500 font-medium leading-relaxed">{{ $pharmacyModule->description ?? __('Health essentials and medicines right when you need them.') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($serviceModule)
                <div class="bento-card col-span-12 group cursor-pointer h-auto sm:h-[250px] shadow-sm bg-white border-slate-100 mt-4 overflow-hidden relative">
                    <div class="blob blob-service" style="background: {{ $serviceModule->color ?? '#8b5cf6' }}; opacity:0.3; width:550px; height:550px; top:-150px; left:-150px;"></div>
                    <div class="card-content flex flex-col sm:flex-row items-center justify-between h-full text-dark gap-8 relative z-10">
                        <div class="flex-1">
                            <h3 class="text-3xl md:text-4xl font-extrabold mb-3 text-slate-900">{{ $serviceModule->name }}</h3>
                            <p class="text-slate-500 font-medium text-lg max-w-lg leading-relaxed">{{ $serviceModule->description ?? __('Need a plumber, cleaner, or electrician? Book trusted professionals instantly.') }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="w-32 h-32 bg-slate-50 rounded-[32px] flex items-center justify-center text-6xl shadow-sm transition-transform group-hover:rotate-12 group-hover:scale-110 border border-slate-100" style="color: {{ $serviceModule->color ?? '#8b5cf6' }};">
                                🔧
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </section>

    <!-- Endless Marquee Section -->
    <section class="py-12 border-y border-slate-200 bg-white overflow-hidden">
        <div class="marquee-container max-w-screen-2xl mx-auto">
            <div class="marquee-content items-center">
                <!-- Group 1 -->
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ __('Fast Delivery') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-super-primary tracking-tight">{{ __('Secure Payments') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ __('Verified Drivers') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-blue-500 tracking-tight">{{ __('Live Tracking') }}</span>
                
                <!-- Group 2 (Duplicate for seamless scroll) -->
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ __('Fast Delivery') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-super-primary tracking-tight">{{ __('Secure Payments') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ __('Verified Drivers') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-blue-500 tracking-tight">{{ __('Live Tracking') }}</span>
            </div>
            
            <div class="marquee-content items-center" aria-hidden="true">
                <!-- Group 1 -->
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ __('Fast Delivery') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-super-primary tracking-tight">{{ __('Secure Payments') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ __('Verified Drivers') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-blue-500 tracking-tight">{{ __('Live Tracking') }}</span>
                
                <!-- Group 2 (Duplicate for seamless scroll) -->
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ __('Fast Delivery') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-super-primary tracking-tight">{{ __('Secure Payments') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-slate-900 tracking-tight">{{ __('Verified Drivers') }}</span>
                <span class="text-3xl font-extrabold text-slate-300 mx-4">•</span>
                <span class="text-4xl font-extrabold text-blue-500 tracking-tight">{{ __('Live Tracking') }}</span>
            </div>
        </div>
    </section>

    <!-- Partner CTA -->
    <section id="business" class="py-24 px-4 md:px-6">
        <div class="max-w-7xl mx-auto">
            <div class="bg-zinc-950 rounded-[3rem] p-10 md:p-16 lg:p-24 relative overflow-hidden text-center md:text-left flex flex-col md:flex-row items-center justify-between gap-12 shadow-2xl border border-white/5">
                <!-- Background decor - Vibrant and high-opacity as requested -->
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-super-primary rounded-full filter blur-[120px] opacity-30 -translate-y-1/2 translate-x-1/4"></div>
                <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-600 rounded-full filter blur-[120px] opacity-20 translate-y-1/2 -translate-x-1/4"></div>
                
                <div class="relative z-10 md:w-2/3">
                    <h2 class="text-4xl md:text-6xl font-extrabold text-white mb-6 tracking-tight leading-tight">
                        {{ __('Grow your business with us') }}
                    </h2>
                    <p class="text-xl md:text-2xl text-white/70 font-medium mb-10 max-w-2xl leading-relaxed">
                        {{ __('Join thousands of merchants and drivers who are earning more every day. The best platform to scale your operations.') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-5 justify-center md:justify-start">
                        <a href="{{ route('register.vendor') }}" class="pill-btn bg-white text-zinc-950 hover:bg-slate-100 text-lg px-10 py-5 font-bold shadow-xl transition-all hover:scale-105">
                            {{ __('Become a Partner') }}
                        </a>
                        <a href="{{ route('register.driver') }}" class="pill-btn border-2 border-white/20 text-white hover:bg-white/10 text-lg px-10 py-5 font-bold transition-all hover:border-white/40">
                            {{ __('Drive & Earn') }}
                        </a>
                    </div>
                </div>
                
                <div class="relative z-10 w-48 h-48 md:w-72 md:h-72 flex-shrink-0 animate-[float_8s_ease-in-out_infinite]">
                    <!-- Abstract 3D-like generic icon for partnership -->
                    <div class="w-full h-full bg-gradient-to-br from-super-primary to-blue-500 rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] flex items-center justify-center transform rotate-12 border border-white/10 backdrop-blur-sm">
                        <span class="text-9xl drop-shadow-2xl">🚀</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Ready to get started -->
    <section class="py-32 px-4 md:px-6 relative overflow-hidden">
        <!-- Background Blobs for consistency -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-super-primary/5 rounded-full filter blur-[120px] -z-10"></div>
        <div class="absolute bottom-0 right-1/4 w-[400px] h-[400px] bg-blue-500/5 rounded-full filter blur-[100px] -z-10"></div>

        <div class="max-w-4xl mx-auto text-center relative z-10">
            <div class="w-24 h-24 rounded-[2rem] mx-auto bg-white shadow-2xl flex items-center justify-center border border-slate-50 mb-10 transform -rotate-6 p-4">
                <img src="{{ appLogo() }}" alt="{{ $appName }}" class="w-full h-full object-contain" />
            </div>
            <h2 class="text-5xl md:text-7xl font-extrabold text-dark tracking-tight mb-6 leading-tight">
                {{ __('Ready to get started?') }}
            </h2>
            <p class="text-xl md:text-2xl text-slate-500 font-medium max-w-2xl mx-auto mb-12">
                {{ __('Download the app today and experience the ultimate convenience of everything delivered.') }}
            </p>
            
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                <!-- Google Play Button -->
                <a href="https://play.google.com/store/apps/details?id={{ env('dynamic_link.android') }}" class="pill-btn bg-[#000000] text-white hover:bg-[#1a1a1a] text-lg px-10 py-5 shadow-2xl w-full sm:w-auto transform transition-all hover:scale-105 group">
                    <x-tabler-brand-android class="w-8 h-8 mr-3 text-[#3DDC84]" />
                    <div class="text-left">
                        <div class="text-[10px] uppercase font-bold text-slate-400 leading-none mb-1">{{ __('Get it on') }}</div>
                        <div class="text-xl font-bold leading-none">{{ __('Google Play') }}</div>
                    </div>
                </a>
                
                <!-- App Store Button -->
                <a href="https://apps.apple.com/app/{{ env('dynamic_link.ios_id') }}" class="pill-btn bg-[#000000] text-white hover:bg-[#1a1a1a] text-lg px-10 py-5 shadow-2xl w-full sm:w-auto transform transition-all hover:scale-105 group">
                    <x-tabler-brand-apple class="w-8 h-8 mr-3 text-white" />
                    <div class="text-left">
                        <div class="text-[10px] uppercase font-bold text-slate-400 leading-none mb-1">{{ __('Download on the') }}</div>
                        <div class="text-xl font-bold leading-none">{{ __('App Store') }}</div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white pt-20 pb-10 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 lg:gap-8 mb-16">
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center gap-3">
                        <img src="{{ appLogo() }}" alt="{{ $appName }}" class="w-10 h-10 object-contain" />
                        <span class="text-xl font-extrabold tracking-tight">{{ $appName }}</span>
                    </div>
                    <p class="text-slate-500 font-medium max-w-sm">
                        {!! nl2br(e(setting('website.modern.websiteFooterBrief', 'Your local community, delivered.'))) !!}
                    </p>
                </div>
                
                <div>
                    <h4 class="font-bold text-dark mb-6 tracking-wide uppercase text-sm">{{ __('Platform') }}</h4>
                    <ul class="space-y-4 font-medium text-slate-500">
                        <li><a href="#services" class="hover:text-super-primary transition-colors">{{ __('Our Services') }}</a></li>
                        <li><a href="{{ route('register.driver') }}" class="hover:text-super-primary transition-colors">{{ __('Become a Driver') }}</a></li>
                        <li><a href="{{ route('register.vendor') }}" class="hover:text-super-primary transition-colors">{{ __('Become a Partner') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-dark mb-6 tracking-wide uppercase text-sm">{{ __('Support') }}</h4>
                    <ul class="space-y-4 font-medium text-slate-500">
                        <li><a href="{{ route('contact') }}" class="hover:text-super-primary transition-colors">{{ __('Contact Us') }}</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-super-primary transition-colors">{{ __('Terms of Service') }}</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-super-primary transition-colors">{{ __('Privacy Policy') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-dark mb-6 tracking-wide uppercase text-sm">{{ __('Follow Us') }}</h4>
                    <div class="flex gap-4">
                        @if(setting('website.modern.social.fbLink', ''))
                            <a href="{{ setting('website.modern.social.fbLink', '') }}" class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-colors">FB</a>
                        @endif
                        @if(setting('website.modern.social.twLink', ''))
                            <a href="{{ setting('website.modern.social.twLink', '') }}" class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-sky-500 hover:text-white transition-colors">TW</a>
                        @endif
                        @if(setting('website.modern.social.igLink', ''))
                            <a href="{{ setting('website.modern.social.igLink', '') }}" class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-pink-600 hover:text-white transition-colors">IG</a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="pt-8 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4 text-sm font-medium text-slate-400">
                <p>&copy; {{ date('Y') }} {{ $appName }}. {{ __('All rights reserved') }}.</p>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                    <span>All systems operational</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll
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

        // Dynamic Nav Shadow
        const nav = document.querySelector('nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                nav.style.boxShadow = '0 10px 30px -10px rgba(0,0,0,0.05)';
            } else {
                nav.style.boxShadow = 'none';
            }
        });
    </script>
@endsection
