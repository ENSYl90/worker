@section('title', __('Become A Partner'))
@php
    $colors = getAppColorShades();
    $accentColor = $colors['shades'][500];
    $totalSteps = 2; // Business and Personal
@endphp


<div class="min-h-screen bg-white flex flex-col font-sans selection:bg-primary-100 selection:text-primary-900">

    <!-- Top Navigation (Fixed) -->
    <nav
        class="fixed top-0 left-0 right-0 z-50 px-6 py-5 flex items-center justify-between bg-white/90 backdrop-blur-md border-b border-gray-100">
        <div class="flex items-center gap-3 cursor-pointer" onclick="window.location.href='/'">
            <img src="{{ appLogo() }}" title="{{ appName() }}" class="w-8 h-8 object-contain" />
            <span class="font-bold text-xl tracking-tight text-gray-900">{{ appName() }}</span>
        </div>
        <div class="flex items-center gap-6">
            <div class="hidden md:block">
                <livewire:select.language-selector />
            </div>
            <a href="{{ route('login') }}"
                class="text-sm font-bold text-gray-400 hover:text-gray-900 transition-colors flex items-center gap-1">
                <x-tabler-x class="w-4 h-4" />
                {{ __('Cancel') }}
            </a>
        </div>
    </nav>

    <!-- Main Content Area (Significant top padding) -->
    <div class="flex-1 flex flex-col items-center justify-start p-6 lg:p-12 pt-32 lg:pt-40">

        <div class="w-full max-w-xl">

            <!-- Circular Step Indicator Header -->
            <div class="flex items-end justify-between mb-12">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                        {{ __('Create your business account') }}
                    </h1>
                    <p class="text-gray-400 text-base">
                        {{ __('Step :step of :total: :title', ['step' => $currentStep, 'total' => $totalSteps, 'title' => $currentStep == 1 ? __('Business Information') : __('Personal Information')]) }}
                    </p>
                </div>

                <div class="relative w-14 h-14 flex items-center justify-center flex-shrink-0">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="28" cy="28" r="25" stroke="currentColor" stroke-width="3" fill="transparent"
                            class="text-gray-100" />
                        <circle cx="28" cy="28" r="25" stroke="currentColor" stroke-width="3" fill="transparent"
                            class="text-primary-500 transition-all duration-500" stroke-dasharray="157"
                            stroke-dashoffset="{{ 157 - (157 * ($currentStep / $totalSteps)) }}"
                            stroke-linecap="round" />
                    </svg>
                    <div
                        class="absolute inset-0 flex items-center justify-center text-[10px] uppercase font-black text-gray-900">
                        {{ $currentStep }}/{{ $totalSteps }}
                    </div>
                </div>
            </div>

            <!-- Form Content (Flatter) -->
            <div class="space-y-10">

                @if($currentStep == 1)
                    <div class="space-y-8 animate-fade-in">
                        <div class="grid grid-cols-1 gap-6">
                            <x-input title="{{ __('Business Name') }}" name="vendor_name"
                                placeholder="{{ __('Company Ltd') }}" class="nomba-input-flat" />
                            <x-select title="{{ __('Business Type') }}" :options='$vendorTypes ?? []' name="vendor_type_id"
                                :defer="false" class="nomba-input-flat" />

                            <div class="space-y-4">
                                <label
                                    class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Location Details') }}</label>
                                <div class="space-y-4">
                                    <livewire:component.autocomplete-address title="{{ __('Physical Address') }}"
                                        name="address" />
                                    <x-input-error message="{{ $errors->first('address') }}" />
                                    <div class="grid grid-cols-2 gap-4">
                                        <x-input title="{{ __('Latitude') }}" name="latitude" placeholder="0.000" />
                                        <x-input title="{{ __('Longitude') }}" name="longitude" placeholder="0.000" />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-input title="{{ __('Business Email') }}" name="vendor_email"
                                    placeholder="info@business.com" />
                                <x-phoneselector model="vendor_phone" />
                            </div>

                            <div class="pt-6 space-y-4">
                                <label
                                    class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Documents') }}</label>
                                <div class="text-xs text-gray-500 leading-relaxed italic mb-2">
                                    {!! setting('page.settings.vendorDocumentInstructions', __('Please upload your business registration documents.')) !!}
                                </div>
                                <div class="border border-gray-100 rounded-2xl p-4 bg-gray-50/30">
                                    <livewire:component.multiple-media-upload types="PNG, JPG, PDF"
                                        fileTypes="image/*,application/pdf" emitFunction="vendorDocumentsUploaded"
                                        max="{{ setting('page.settings.vendorDocumentCount', 3) }}" />
                                </div>
                                <x-input-error message="{{ $errors->first('vendorDocuments') }}" />
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-8 animate-fade-in">
                        <div class="grid grid-cols-1 gap-6">
                            <x-input title="{{ __('Your Full Name') }}" name="name" placeholder="John Doe" />
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-input title="{{ __('Personal Email') }}" name="email" placeholder="john@example.com" />
                                <x-phoneselector />
                            </div>
                            <x-input-password title="{{ __('Password') }}" name="password" />

                            <div class="mt-8 flex items-start gap-4">
                                <div class="pt-1">
                                    <x-checkbox name="agreedVendor" :defer="false" :noMargin="true"
                                        class="w-5 h-5 accent-primary-500" />
                                </div>
                                <label for="agreedVendor" class="text-sm text-gray-500 leading-relaxed select-none">
                                    {{ __('I agree to the') }}
                                    <a href="#"
                                        class="font-bold text-gray-900 hover:text-primary-600 transition-colors">{{ __('Terms of Service') }}</a>
                                    {{ __('and') }}
                                    <a href="#"
                                        class="font-bold text-gray-900 hover:text-primary-600 transition-colors">{{ __('Privacy Policy') }}</a>.
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons (Flat but bold) -->
                <div class="mt-12 flex flex-col sm:flex-row gap-4">
                    @if($currentStep > 1)
                        <button type="button" wire:click="previousStep"
                            class="w-full sm:w-1/3 py-4 rounded-xl border border-gray-200 text-gray-500 font-bold hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 flex items-center justify-center gap-2">
                            <x-tabler-arrow-left class="w-5 h-5" />
                            {{ __('Back') }}
                        </button>
                    @endif

                    @if($currentStep < $totalSteps)
                        <button type="button" wire:click="nextStep"
                            class="flex-1 py-4 bg-primary-500 text-white font-bold rounded-xl active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 group">
                            {{ __('Continue') }}
                            <x-tabler-arrow-right class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                        </button>
                    @else
                        <button type="button" wire:click="signUp"
                            class="flex-1 py-4 bg-primary-500 text-white font-bold rounded-xl active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2 group">
                            {{ __('Create Account') }}
                            <x-tabler-sparkles class="w-5 h-5" />
                        </button>
                    @endif
                </div>

                <div class="mt-12 text-center">
                    <p class="text-sm text-gray-400">
                        {{ __('Already have an account?') }}
                        <a href="{{ route('login') }}"
                            class="text-gray-900 font-bold hover:text-primary-600 transition-colors ml-1">
                            {{ __('Login') }}
                        </a>
                    </p>
                </div>
            </div>

            <!-- Global Footer -->
            @if (env("SHOW_SYSTEM_VERSION", true))
                <div class="mt-20 mb-10 text-center opacity-40">
                    <p class="text-[10px] font-bold text-gray-900 uppercase tracking-[0.2em]">
                        &copy; {{ date('Y') }} {{ appName() }}. {{ __('Secure business infrastructure.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    <x-loading />
</div>


@push('styles')
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
            background-color: white !important;
        }

        /* Nomba Flat Input Styling */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            background-color: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 0.75rem !important;
            padding: 0.875rem 1rem !important;
            font-size: 0.95rem !important;
            font-weight: 500 !important;
            color: #1A202C !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }

        input:focus,
        select:focus {
            border-color: var(--primary-500, #3B82F6) !important;
            outline: none !important;
            background-color: #F8FAFC !important;
        }

        /* Phone input overlap fix */
        .iti input,
        .iti input[type="text"] {
            padding-left: 3.5rem !important;
        }

        /* Label styling override */
        label {
            color: #718096 !important;
            font-size: 0.8rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-bottom: 0.5rem !important;
            display: block !important;
        }

        /* Hide the default wizard indicator and buttons */
        .space-y-6.p-6 {
            padding: 0 !important;
        }

        .space-y-6.p-6>div:first-child,
        .space-y-6.p-6>div:last-child {
            display: none !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush