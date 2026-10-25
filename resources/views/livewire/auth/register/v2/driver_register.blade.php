@section('title', __('Drive with :app_name', ['app_name' => appName()]))
@php
    $colors = getAppColorShades();
    $accentColor = $colors['shades'][500];
    $totalSteps = 2; // Personal and Other Info
@endphp


<div class="min-h-screen bg-white flex flex-col font-sans selection:bg-primary-100 selection:text-primary-900">
    @if (setting('partnersCanRegister', true))

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

                <!-- Header & Indicator -->
                <div class="flex items-end justify-between mb-12">
                    <div class="space-y-1">
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                            {{ __('Join our local fleet') }}
                        </h1>
                        <p class="text-gray-400 text-base">
                            @php
                                $stepTitle = $currentStep == 1 ? __('Account Profile') : __('Vehicle & Verification');
                            @endphp
                            {{ __('Step :step of :total: :title', ['step' => $currentStep, 'total' => $totalSteps, 'title' => $stepTitle]) }}
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
                                <x-input title="{{ __('Full Name') }}" name="name" placeholder="John Doe" />
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-input title="{{ __('Email Address') }}" name="email" placeholder="john@example.com" />
                                    <x-phoneselector />
                                </div>
                                <x-input-password title="{{ __('Password') }}" name="password" />
                                <div class="pt-4">
                                    <x-input title="{{ __('Referral Code') }}" name="referalCode"
                                        placeholder="{{ __('Optional') }}" />
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="space-y-8 animate-fade-in">
                            <div class="grid grid-cols-1 gap-6">
                                <x-select :options="$driverTypes ?? []" name="driverType" title="{{ __('Driver Group') }}"
                                    :defer="false" />

                                {{-- taxi driver section --}}
                                <div class="{{ $driverType == 'taxi' ? 'block' : 'hidden' }} space-y-6">
                                    <div class="space-y-6 mt-4">
                                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                                            {{ __('Vehicle Specifications') }}
                                        </label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <x-select title="{{ __('Make') }}" name="car_make_id" :options="$this->car_makes ?? []" :defer="false" :noPreSelect="true" />
                                            <x-select title="{{ __('Model') }}" name="car_model_id" :options="$this->car_models ?? []" :noPreSelect="true" />
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <x-input title="{{ __('Plate No.') }}" name="reg_no" placeholder="ABC-123" />
                                            <x-input title="{{ __('Color') }}" name="color" placeholder="Silver" />
                                        </div>
                                        <x-select title="{{ __('Service Category') }}" :options='$vehicleTypes ?? []'
                                            name="vehicle_type_id" :noPreSelect="true" />
                                    </div>
                                </div>

                                <div class="pt-6 space-y-4">
                                    <label
                                        class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Documents') }}</label>
                                    <div class="text-xs text-gray-500 leading-relaxed italic mb-2">
                                        {!! setting('page.settings.driverDocumentInstructions', __('Please upload your driving license and vehicle registration.')) !!}
                                    </div>
                                    <div class="border border-gray-100 rounded-2xl p-4 bg-gray-50/30">
                                        <livewire:component.multiple-media-upload types="PNG, JPG, PDF"
                                            fileTypes="image/*,application/pdf" emitFunction="driverDocumentsUploaded"
                                            max="{{ setting('page.settings.driverDocumentCount', 3) }}" />
                                    </div>
                                    <x-input-error message="{{ $errors->first('driverDocuments') }}" />
                                </div>

                                <div class="mt-8 flex items-start gap-4">
                                    <div class="pt-1">
                                        <x-checkbox name="agreedDriver" :defer="false" :noMargin="true"
                                            class="w-5 h-5 accent-primary-500" />
                                    </div>
                                    <label for="agreedDriver" class="text-sm text-gray-500 leading-relaxed select-none">
                                        {{ __('I certify that all information is accurate and I agree to the') }}
                                        <a href="{{ route('terms') }}" target="_blank"
                                            class="font-bold text-gray-900 hover:text-primary-600 transition-colors">
                                            {{ __('Operating Terms') }}
                                        </a>.
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
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
                                {{ __('Complete Application') }}
                                <x-tabler-steering-wheel class="w-5 h-5" />
                            </button>
                        @endif
                    </div>

                </div>

                <!-- Global Footer -->
                @if (env("SHOW_SYSTEM_VERSION", true))
                    <div class="mt-20 mb-10 text-center opacity-40">
                        <p class="text-[10px] font-bold text-gray-900 uppercase tracking-[0.2em]">
                            &copy; {{ date('Y') }} {{ appName() }}. {{ __('Empowering flexible livelihoods.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

    @else
        {{-- Registration disabled --}}
        <div class="min-h-screen bg-white flex flex-col items-center justify-center p-6 font-sans">
            <div class="p-12 max-w-md w-full text-center">
                <div
                    class="w-20 h-20 rounded-3xl bg-gray-50 flex items-center justify-center mx-auto mb-8 border border-gray-100">
                    <x-tabler-lock class="w-10 h-10 text-gray-400" />
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4 tracking-tight">{{ __('Applications Closed') }}</h2>
                <p class="text-base text-gray-500 leading-relaxed mb-10 font-medium">
                    {{ __('We are currently at full capacity for driver partners. Please check back later or get in touch.') }}
                </p>
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center gap-3 px-8 py-4 bg-primary-500 text-white rounded-xl text-sm font-bold hover:bg-primary-600 transition-all duration-300">
                    <x-tabler-mail class="w-5 h-5" />
                    {{ __('Contact Support') }}
                </a>
            </div>
            <p class="mt-8 text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">&copy; {{ date('Y') }}
                {{ appName() }}
            </p>
        </div>
    @endif
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

        /* Override wizard indicator */
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