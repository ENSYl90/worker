@section('title', __('Dashboard'))
<div>
    @if (auth()->user()->hasAnyRole('admin'))
        <livewire:component.dashboard.admin />
    @elseif (auth()->user()->hasAnyRole('manager'))
        <livewire:component.dashboard.manager />
    @elseif (auth()->user()->hasAnyRole('city-admin'))
        <livewire:component.dashboard.city-admin />
    @elseif (auth()->user()->hasAnyRole('fleet-manager'))
        <livewire:component.dashboard.fleet-manager />
    @else
        <x-baseview title="{{ __('Dashboard') }}">
            <div class="mt-10 grid min-h-[58vh] place-items-center">
                <section class="w-full max-w-3xl overflow-hidden rounded-lg border border-gray-100 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50 px-6 py-5 sm:px-8">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 flex-none items-center justify-center rounded-lg bg-primary-100 text-primary-700">
                                <x-heroicon-s-lock-closed class="h-6 w-6" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-primary-700">{{ __('Access pending') }}</p>
                                <h2 class="mt-1 text-xl font-semibold text-gray-900 sm:text-2xl">
                                    {{ __('No dashboard is available for this account') }}
                                </h2>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-6 sm:px-8">
                        <p class="max-w-2xl text-sm leading-6 text-gray-600">
                            {{ __('Your account is active, but it is not assigned to a dashboard role yet. Please contact an administrator to update your role or permissions.') }}
                        </p>

                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Current role') }}</p>
                                <p class="mt-2 truncate text-sm font-semibold text-gray-900">
                                    {{ auth()->user()->role_name ?: __('Not assigned') }}
                                </p>
                            </div>
                            <div class="rounded-md border border-gray-100 bg-gray-50 p-4">
                                <p class="text-xs font-semibold uppercase text-gray-500">{{ __('Status') }}</p>
                                <p class="mt-2 text-sm font-semibold text-gray-900">{{ __('Waiting for setup') }}</p>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('profile') }}"
                                class="inline-flex h-11 items-center justify-center rounded-md bg-primary-600 px-5 text-sm font-semibold text-theme transition duration-200 hover:bg-primary-700 focus:outline-none focus:shadow-outline-primary">
                                {{ __('View profile') }}
                            </a>
                            <a href="{{ route('web.logout') }}"
                                class="inline-flex h-11 items-center justify-center rounded-md border border-gray-200 bg-white px-5 text-sm font-semibold text-gray-700 transition duration-200 hover:bg-gray-50 focus:outline-none focus:shadow-outline-primary">
                                {{ __('Sign out') }}
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </x-baseview>
    @endif
</div>
@push('scripts')
    @livewireChartsScripts
@endpush
