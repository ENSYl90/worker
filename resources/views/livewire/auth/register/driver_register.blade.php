@section('title', __('Drive with :app_name', ['app_name' => appName()]))
@php
    $colors = getAppColorShades();
    $bgColorStart = $colors['shades'][400];
    $bgColorEnd = $colors['shades'][700];
@endphp


<div>
    @if (setting('partnersCanRegister', true))
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
                                {{ __('Join Our Growing Network of Drivers') }}
                            </h1>
                            <p class="text-xl text-white/90 mb-8">
                                {{ __('Whether you\'re a taxi or personal driver, we make it easy to earn and grow with us.') }}
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
                                <div
                                    class="w-16 h-16 bg-primary-500 rounded-xl flex items-center justify-center mx-auto mb-4">
                                    <img src="{{ appLogo() }}" title="{{ appName() }}" />

                                </div>
                                <livewire:select.language-selector />
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                    {{ __('Drive. Earn. Grow.') }}
                                </h2>

                                <p class="text-gray-600">
                                    {{ __('Create your account and start your journey with us today.') }}
                                </p>
                            </div>

                            <!-- Registration Form -->
                            <div class="space-y-6">

                                <x-wizard :steps="[__('Personal'), __('Other Info')]" :currentStep="$currentStep" finishLabel="{{ __('Create Account') }}"
                                    finishAction="signUp">
                                    <x-wizard.step step="1">
                                        <p class="font-semibold text-2xl">{{ __('Personal Details') }}</p>
                                        <x-input title="{{ __('Name') }}" name="name" placeholder="John" />
                                        <div class="grid grid-cols-1 gap-0 md:gap-4 md:grid-cols-2">
                                            <x-input title="{{ __('Email') }}" name="email"
                                                placeholder="info@mail.com" />
                                            <x-phoneselector />
                                        </div>
                                        <x-input-password title="{{ __('Login Password') }}" name="password" />
                                        <x-input title="{{ __('Referral Code') }}" name="referalCode" placeholder="" />
                                    </x-wizard.step>
                                    <x-wizard.step step="2">
                                        <x-select :options="$driverTypes ?? []" name="driverType" title="{{ __('Driver Type') }}"
                                            :defer="false" />
                                        {{-- taxi driver section --}}
                                        <div class="{{ $driverType == 'taxi' ? 'block' : 'hidden' }}">
                                            <hr class="my-4" />
                                            <p class="font-semibold text-2xl">{{ __('Vehicle Details') }}</p>
                                            <div class="grid grid-cols-2 gap-4">
                                                {{-- car make --}}
                                                <x-select title="{{ __('Vehicle Make') }}" name="car_make_id"
                                                    :options="$this->car_makes ?? []" :defer="false" :noPreSelect="true" />

                                                {{-- car model --}}
                                                <x-select title="{{ __('Vehicle Model') }}" name="car_model_id"
                                                    :options="$this->car_models ?? []" :noPreSelect="true" />
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <x-input title="{{ __('Registration Number') }}" name="reg_no" />
                                                <x-input title="{{ __('Color') }}" name="color" />
                                                {{-- vehicle type --}}
                                                <x-select title="{{ __('Vehicle Type') }}" :options='$vehicleTypes ?? []'
                                                    name="vehicle_type_id" :noPreSelect="true" />
                                            </div>

                                        </div>
                                        <hr class="my-4" />
                                        <p class="font-light">{{ __('Documents') }}</p>
                                        <div>
                                            {!! setting('page.settings.driverDocumentInstructions', '') !!}
                                        </div>
                                        <livewire:component.multiple-media-upload types="PNG or JPEG"
                                            fileTypes="image/*" emitFunction="driverDocumentsUploaded"
                                            max="{{ setting('page.settings.driverDocumentCount', 3) }}" />
                                        <x-input-error message="{{ $errors->first('driverDocuments') }}" />
                                        <hr class="my-4" />
                                        <div class="flex items-center my-3">
                                            <x-checkbox name="agreedDriver" :defer="false" :noMargin="true">
                                                <span>{{ __('I agree with') }} <a href="{{ route('terms') }}"
                                                        target="_blank"
                                                        class="font-bold text-primary-500 hover:underline">{{ __('terms and conditions') }}</a></span>
                                            </x-checkbox>
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



        </div>
    @else
        {{-- Registration disabled  --}}
        <div class="w-full p-12 mx-auto my-12 rounded-sm shadow md:w-5/12 lg:w-4/12 ">
            <p class="mb-2 text-2xl font-semibold">{{ __('Registration Page Not available') }}</p>
            <p class="text-sm">
                {{ __('Partner account registration is currently unavailable. Please stay tune/contact support regarding further instruction about registering for a partners account. Thank you') }}
            </p>
            <p class='mt-4 text-center'>
                <a href="{{ route('contact') }}" class="underline text-primary-600">
                    {{ __('Contact Us') }}
                </a>
            </p>
        </div>
    @endif

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
                background: linear-gradient(135deg, {{ $bgColorStart }} 0%, {{ $bgColorEnd }} 100%);
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

        <script>
            $(document).ready(function() {
                //WHEN THE IFRAME HAS LOADED
                $('iframe').ready(function() {
                    //timeout to allow iframe to load
                    setTimeout(function() {
                        //SET THE HEIGHT OF THE IFRAME
                        var height = $('#iframe').contents().find('body').height();
                        //if its zero
                        if (height > 30) {
                            height += 30;
                        }
                        $('#iframe').height(height);
                    }, 100);
                });
            });
        </script>
    @endpush




    <x-loading />

</div>