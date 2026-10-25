@section('title', 'Login')

@php
    $appName = setting('websiteName', 'Glover');
    $loginImage = getValidValue(setting('loginImage'), asset('images/login.jpeg'));
@endphp

<main class="min-h-screen bg-slate-50 text-slate-950 lg:h-screen lg:overflow-hidden">
    <div class="min-h-screen lg:h-screen">
        <section class="relative hidden overflow-hidden lg:fixed lg:inset-y-0 lg:left-0 lg:block lg:w-1/2">
            <img src="{{ $loginImage }}" alt="{{ $appName }} {{ __('login background') }}"
                class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-slate-950 opacity-60"></div>
            <div class="absolute inset-0 bg-primary-900 opacity-25"></div>

            <div class="relative flex h-full flex-col justify-between p-10 xl:p-14">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center bg-white">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ $appName }}"
                            class="h-8 w-8 object-contain" />
                    </span>
                    <div>
                        <p class="text-lg font-semibold text-white">{{ $appName }}</p>
                        <p class="text-sm text-white text-opacity-70">{{ __('Business console') }}</p>
                    </div>
                </div>

                <div class="max-w-xl">
                    <p class="mb-4 inline-flex rounded-full border border-white border-opacity-20 bg-white bg-opacity-10 px-4 py-2 text-sm font-medium text-white text-opacity-90 backdrop-blur">
                        {{ __('Orders, vendors, drivers, and services in one place') }}
                    </p>
                    <h1 class="text-4xl font-semibold leading-tight text-white xl:text-5xl">
                        {{ __('Welcome back') }}
                    </h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-white text-opacity-80">
                        {{ __('Sign in to continue managing your marketplace operations with clarity and control.') }}
                    </p>
                </div>

                @if (env("SHOW_SYSTEM_VERSION", true))
                    <p class="text-sm text-white text-opacity-60">
                        {{ __('Release') }} {{ setting('appVerison', '1.0.0') }}
                    </p>
                @endif
            </div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-6 sm:px-8 lg:ml-auto lg:h-screen lg:w-1/2 lg:items-start lg:overflow-y-auto lg:px-16 lg:py-10">
            <div class="flex w-full max-w-xl flex-col justify-center">
                <div class="mb-8 flex items-center justify-between gap-4 lg:hidden">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center bg-white">
                            <img src="{{ asset('images/logo.png') }}" alt="{{ $appName }}"
                                class="h-8 w-8 object-contain" />
                        </span>
                        <div>
                            <p class="text-lg font-semibold text-slate-950">{{ $appName }}</p>
                            <p class="text-sm text-slate-500">{{ __('Business console') }}</p>
                        </div>
                    </div>
                    <div class="min-w-32">
                        <livewire:select.language-selector />
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 sm:p-8 lg:p-10">
                    <div class="mb-8 hidden items-center justify-between gap-4 lg:flex">
                        <div>
                            <p class="text-sm font-medium text-primary-600">{{ $appName }}</p>
                            <h2 class="mt-2 text-3xl font-semibold text-slate-950">{{ __('Sign in') }}</h2>
                        </div>
                        <div class="min-w-32">
                            <livewire:select.language-selector />
                        </div>
                    </div>

                    <div class="mb-8 lg:hidden">
                        <p class="text-sm font-medium text-primary-600">{{ __('Secure access') }}</p>
                        <h1 class="mt-2 text-3xl font-semibold text-slate-950">{{ __('Sign in') }}</h1>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ __('Continue to your marketplace dashboard.') }}
                        </p>
                    </div>

                    <form wire:submit.prevent="login" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">
                                {{ __('Email Address') }}
                            </label>
                            <input id="email" type="email" name="email" autocomplete="email"
                                placeholder="admin@demo.com" wire:model.defer="email"
                                class="h-12 w-full rounded-md border border-slate-300 bg-white px-4 text-base text-slate-950 transition duration-200 placeholder:text-slate-400 focus:border-primary-500 focus:outline-none focus:shadow-outline-primary" />
                            @error('email')
                                <p class="mt-2 text-sm font-medium text-red-700" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-data="{ showPassword: false }">
                            <label for="password" class="mb-2 block text-sm font-medium text-slate-700">
                                {{ __('Password') }}
                            </label>
                            <div class="relative">
                                <input id="password" name="password" autocomplete="current-password"
                                    :type="showPassword ? 'text' : 'password'" placeholder="********"
                                    wire:model.defer="password"
                                    class="h-12 w-full rounded-md border border-slate-300 bg-white py-3 pl-4 pr-12 text-base text-slate-950 transition duration-200 placeholder:text-slate-400 focus:border-primary-500 focus:outline-none focus:shadow-outline-primary" />
                                <button type="button" @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                                    class="absolute inset-y-0 right-1 flex h-11 w-11 items-center justify-center rounded-md text-slate-500 transition duration-200 hover:text-slate-800 focus:outline-none focus:shadow-outline-primary">
                                    <x-tabler-eye class="h-5 w-5" x-show="!showPassword" />
                                    <x-tabler-eye-off class="h-5 w-5" x-show="showPassword" x-cloak />
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm font-medium text-red-700" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="hidden">
                            <x-input title="" type="" placeholder="" name="fcmToken" />
                        </div>

                        <div class="flex flex-col gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between">
                            <label for="remember" class="flex h-11 items-center gap-3 text-sm font-medium text-slate-700">
                                <input id="remember" type="checkbox" value="1" wire:model.defer="remember"
                                    class="h-5 w-5 rounded border-slate-300 text-primary-600 focus:border-primary-500 focus:shadow-outline-primary" />
                                <span>{{ __('Stay signed in') }}</span>
                            </label>
                            <a class="inline-flex h-11 items-center text-sm font-semibold text-primary-600 underline-offset-4 hover:text-primary-700 hover:underline focus:outline-none focus:shadow-outline-primary"
                                href="{{ route('password.forgot') }}">
                                {{ __('Forgot password?') }}
                            </a>
                        </div>

                        <button type="submit"
                            class="inline-flex h-12 w-full items-center justify-center rounded-md bg-primary-600 px-5 py-3 text-base font-semibold text-theme transition duration-200 hover:bg-primary-700 focus:outline-none focus:shadow-outline-primary disabled:cursor-not-allowed disabled:opacity-60"
                            wire:loading.attr="disabled" wire:target="login">
                            <span wire:loading.remove wire:target="login">{{ __('Login') }}</span>
                            <span wire:loading wire:target="login">{{ __('Signing in...') }}</span>
                        </button>
                    </form>

                    @if (setting('partnersCanRegister', true))
                        <div class="mt-8">
                            <div class="flex items-center gap-3">
                                <div class="h-px flex-1 bg-slate-200"></div>
                                <span class="text-xs font-semibold uppercase text-slate-500">{{ __('Join us') }}</span>
                                <div class="h-px flex-1 bg-slate-200"></div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <a href="{{ route('register.vendor') }}"
                                    class="group flex min-h-[56px] items-center gap-3 rounded-md border border-slate-200 bg-white px-4 py-3 text-slate-800 transition duration-200 hover:border-primary-200 hover:bg-primary-50 focus:outline-none focus:shadow-outline-primary">
                                    <span class="flex h-10 w-10 items-center justify-center text-primary-600">
                                        <x-tabler-home-dollar class="h-5 w-5" />
                                    </span>
                                    <span class="text-sm font-semibold">{{ __('Vendor') }}</span>
                                </a>

                                <a href="{{ route('register.driver') }}"
                                    class="group flex min-h-[56px] items-center gap-3 rounded-md border border-slate-200 bg-white px-4 py-3 text-slate-800 transition duration-200 hover:border-primary-200 hover:bg-primary-50 focus:outline-none focus:shadow-outline-primary">
                                    <span class="flex h-10 w-10 items-center justify-center text-primary-600">
                                        <x-tabler-steering-wheel class="h-5 w-5" />
                                    </span>
                                    <span class="text-sm font-semibold">{{ __('Driver') }}</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                @if (!App::environment('production'))
                    <section class="mt-6 rounded-md border border-slate-200 bg-white p-5">
                        <div class="mb-3 flex items-center justify-between gap-4">
                            <h2 class="text-sm font-semibold text-slate-900">{{ __('Demo Login Accounts') }}</h2>
                            <span class="bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-700">
                                {{ __('Non-production') }}
                            </span>
                        </div>

                        @php
                            $accounts = [
                                ['label' => 'Admin', 'email' => 'admin@demo.com', 'index' => 0],
                                ['label' => 'City Admin', 'email' => 'city-admin@demo.com', 'index' => 3],
                                ['label' => 'Manager', 'email' => 'manager@demo.com', 'index' => 1],
                                ['label' => 'Parcel', 'email' => 'manager1@demo.com', 'index' => 2],
                                ['label' => 'Service', 'email' => 'manager3@demo.com', 'index' => 4],
                                ['label' => 'Client', 'email' => 'client@demo.com', 'index' => 5],
                            ];
                        @endphp

                        <div class="grid grid-cols-1 gap-2">
                            @foreach($accounts as $account)
                                <button type="button" wire:click="loadAccount({{ $account['index'] }})"
                                    @click="navigator.clipboard && navigator.clipboard.writeText('{{ $account['email'] }} | password')"
                                    aria-label="{{ __('Copy') }} {{ $account['label'] }} {{ __('credentials') }}"
                                    class="group flex min-h-[44px] items-center gap-2 rounded-md border border-slate-200 bg-white px-3 text-left transition duration-200 hover:border-primary-200 hover:bg-primary-50 focus:outline-none focus:shadow-outline-primary">
                                    <span class="min-w-0 flex-1 py-2">
                                        <span class="block truncate text-sm text-slate-700">
                                            <span class="font-semibold text-slate-950">{{ $account['label'] }}</span>
                                            <span class="text-slate-400"> · </span>
                                            <span>{{ $account['email'] }}</span>
                                            <span class="text-slate-400"> · </span>
                                            <span>{{ __('password') }}</span>
                                        </span>
                                    </span>
                                    <span class="flex h-9 w-9 flex-none items-center justify-center rounded-md text-slate-500 transition duration-200 group-hover:text-primary-600">
                                        <x-tabler-copy class="h-4 w-4" />
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (env("SHOW_SYSTEM_VERSION", true))
                    <p class="mt-6 text-center text-xs text-slate-500">
                        {{ $appName }} &middot; {{ __('release') }} {{ setting('appVerison', '1.0.0') }}
                    </p>
                @endif
            </div>
        </section>
    </div>

    <x-loading />
</main>
