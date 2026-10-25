@section('title', __('Become A Partner'))
@php
    $colors = getAppColorShades();
    $bgColorStart = $colors['shades'][400];
    $bgColorEnd = $colors['shades'][700];
@endphp


<div class="min-h-screen bg-gray-50">


    <div class="min-h-screen flex">
        <!-- Left Section - Image (40% width) -->
        <div class="hidden lg:flex lg:w-2/5 relative overflow-hidden">
            <div class="absolute inset-0 gradient-bg"></div>
            <div class="relative z-10 flex items-center justify-center w-full p-12">
                <div class="text-center text-white animate-fadeInUp">
                    <div class="mb-8">
                        <div
                            class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-6 glass-effect">
                            <x-tabler-users-group class="w-16 h-16" />
                        </div>
                    </div>
                    <h1 class="text-4xl font-bold mb-4">
                        {{ __('Join Our Community') }}
                    </h1>
                    <p class="text-xl text-white/90 mb-8">
                        {{ __('Create your account and start your journey with us today.') }}
                    </p>
                    <div class="flex justify-center space-x-4 rtl:space-x-reverse">
                        <div class="w-3 h-3 bg-white/50 rounded-full"></div>
                        <div class="w-3 h-3 bg-white rounded-full"></div>
                        <div class="w-3 h-3 bg-white/50 rounded-full"></div>
                    </div>
                </div>
            </div>
            <!-- Decorative elements -->
            <div class="absolute top-10 left-10 w-20 h-20 bg-white/10 rounded-full"></div>
            <div class="absolute bottom-10 right-10 w-32 h-32 bg-white/5 rounded-full"></div>
            <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-white/10 rounded-full"></div>
        </div>

        <!-- Right Section - Form (60% width) -->
        <div class="flex-1 lg:w-3/5 flex items-center justify-center p-6 lg:p-16">
            <div class="w-full max-w-lg lg:max-w-xl">
                <div class="animate-fadeInUp">
                    <!-- Logo/Brand -->
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-primary-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <img src="{{ appLogo() }}" title="{{ __('Logo') }}" />
                        </div>
                        <livewire:select.language-selector />
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            {{ __('Become a partner') }}
                        </h2>
                        <p class="text-gray-600">
                            {{ __('Create your account and start your journey with us today.') }}
                        </p>
                    </div>

                    <!-- Registration Form -->
                    <div class="space-y-6">
                        <x-wizard :steps="[__('Business'), __('Personal')]" :currentStep="$currentStep"
                            finishLabel="{{ __('Create Account') }}" finishAction="signUp">
                            <x-wizard.step step="1">
                                <div>
                                    <h1 class="mb-4 font-semibold text-gray-700 text-md">
                                        {{ __('Business Information') }}
                                    </h1>
                                    <x-input title="{{ __('Business Name') }}" name="vendor_name" placeholder="" />
                                    {{-- vendor type --}}
                                    <x-select title="{{ __('Vendor Type') }}" :options='$vendorTypes ?? []'
                                        name="vendor_type_id" :defer="false" />
                                    <div class="p-2 mt-4 bg-gray-100 border border-gray-300 rounded">
                                        <livewire:component.autocomplete-address title="{{ __('Address') }}"
                                            name="address" />
                                        <x-input-error message="{{ $errors->first('address') }}" />

                                        <div class="grid grid-cols-2 gap-4">
                                            <x-input title="{{ __('Latitude') }}" name="latitude" />
                                            <x-input title="{{ __('Longitude') }}" name="longitude" />
                                        </div>

                                    </div>
                                    <div class="grid grid-cols-1 gap-0 md:gap-4 md:grid-cols-2">
                                        <x-input title="{{ __('Email') }}" name="vendor_email"
                                            placeholder="info@mail.com" />
                                        <x-phoneselector model="vendor_phone" />
                                    </div>

                                    {{-- documents --}}
                                    <hr class="my-4" />
                                    <p class="font-light">{{ __('Documents') }}</p>
                                    <div>
                                        {!! setting('page.settings.vendorDocumentInstructions', '') !!}
                                    </div>
                                    <livewire:component.multiple-media-upload types="PNG or JPEG" fileTypes="image/*"
                                        emitFunction="vendorDocumentsUploaded"
                                        max="{{ setting('page.settings.vendorDocumentCount', 3) }}" />
                                    <x-input-error message="{{ $errors->first('vendorDocuments') }}" />
                                </div>
                            </x-wizard.step>
                            <x-wizard.step step="2">
                                {{-- personal info --}}
                                <div class="space-y-4">
                                    <h1 class="mb-4 font-semibold text-gray-700 text-md">
                                        {{ __('Personal Information') }}
                                    </h1>
                                    <x-input title="{{ __('Name') }}" name="name" placeholder="John" />
                                    <div class="grid grid-cols-2 space-x-4">
                                        <x-input title="{{ __('Email') }}" name="email" placeholder="info@mail.com" />
                                        <x-phoneselector />
                                    </div>
                                    <x-input-password title="{{ __('Login Password') }}" name="password" />

                                    <!-- Terms and Conditions -->
                                    <div class="flex items-center">
                                        <x-checkbox name="agreedVendor" :defer="false" :noMargin="true">
                                            <label for="terms" class="ml-2 text-sm text-gray-600 rtl:mr-2 rtl:ml-0">
                                                <span>{{ __('I agree to the') }}</span>
                                                <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">
                                                    {{ __('Terms of Service') }}
                                                </a>
                                                <span>{{ __('and') }}</span>
                                                <a href="#" class="text-primary-600 hover:text-primary-700 font-medium">
                                                    {{ __('Privacy Policy') }}
                                                </a>
                                            </label>
                                        </x-checkbox>
                                    </div>
                                </div>
                            </x-wizard.step>

                        </x-wizard>

                        <!-- Divider -->
                        <div class="relative my-6">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">

                            </div>
                        </div>

                        <!-- Login Link -->
                        <p class="text-center text-sm text-gray-600 mt-6">
                            <span>
                                {{ __('Already have an account?') }}
                            </span>
                            <a href="{{ route('login') }}"
                                class="text-primary-600 hover:text-primary-700 font-medium ml-1 rtl:mr-1 rtl:ml-0">
                                {{ __('Sign in') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            // Add smooth animations on scroll
            window.addEventListener('scroll', () => {
                const elements = document.querySelectorAll('.animate-fadeInUp');
                elements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;

                    if (elementTop < windowHeight - 100) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }
                });
            });
        </script>
    @endpush


    @push('styles')
        <style>
            /* Custom animations and styles */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-fadeInUp {
                animation: fadeInUp 0.6s ease-out;
            }

            .gradient-bg {
                background: linear-gradient(135deg,
                        {{ $bgColorStart }}
                        0%,
                        {{ $bgColorEnd }}
                        100%);
            }

            .glass-effect {
                backdrop-filter: blur(10px);
                background: rgba(255, 255, 255, 0.1);
            }



            /* RTL specific styles */
            [dir="rtl"] .rtl-flip {
                transform: scaleX(-1);
            }
        </style>
    @endpush
    <x-loading />
</div>