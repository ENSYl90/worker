@php
    $pageTitle = __('Edit Order') . ' #' . ($selectedModel->code ?? '');
@endphp
@section('title', $pageTitle)
<div class="px-4">

    {{-- Header with Back Button --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ $pageTitle }}</h1>
        <a href="{{ route('orders') }}"
           class="inline-flex items-center px-4 py-2 bg-primary-500 hover:bg-primary-600 text-theme font-medium rounded-lg shadow-sm transition-colors duration-200">
            <x-tabler-arrow-left class="w-4 h-4 mr-2" />
            {{ __('Back to Orders') }}
        </a>
    </div>

    @if($selectedModel != null)
        {{-- Order Info Card --}}
        <div class="bg-primary-500 rounded-lg shadow-lg p-6 text-theme mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs opacity-80 uppercase tracking-wide">{{ __('Order Code') }}</p>
                    <p class="text-2xl font-bold">#{{ $selectedModel->code }}</p>
                </div>
                <div>
                    <p class="text-xs opacity-80 uppercase tracking-wide">{{ __('Customer') }}</p>
                    <p class="text-lg font-semibold">{{ $selectedModel->user->name ?? '--' }}</p>
                </div>
                <div>
                    <p class="text-xs opacity-80 uppercase tracking-wide">{{ __('Vendor') }}</p>
                    <p class="text-lg font-semibold">{{ $selectedModel->vendor->name ?? '--' }}</p>
                </div>
                <div>
                    <p class="text-xs opacity-80 uppercase tracking-wide">{{ __('Total Amount') }}</p>
                    <p class="text-lg font-semibold">{{ currencyFormat($selectedModel->total) }}</p>
                </div>
            </div>
        </div>

        {{-- Main Edit Form --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                <x-tabler-edit class="w-5 h-5 mr-2 text-primary-500" />
                {{ __('Edit Order Details') }}
            </h2>

            <div class="space-y-6">

                {{-- Driver Assignment --}}
                @can('order-assign-driver')
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-md font-semibold text-gray-700 mb-4 flex items-center">
                            <x-tabler-user-circle class="w-4 h-4 mr-2 text-primary-500" />
                            {{ __('Delivery Driver Assignment') }}
                        </h3>

                        @if($selectedModel->driver)
                            <div class="mb-4 p-3 bg-primary-50 rounded-lg border border-primary-200">
                                <p class="text-xs text-primary-600 uppercase mb-1">{{ __('Currently Assigned Driver') }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ $selectedModel->driver->name }}</p>
                                <p class="text-xs text-gray-600">{{ $selectedModel->driver->email }}</p>
                            </div>
                        @else
                            <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-sm text-gray-600">{{ __('No driver assigned yet') }}</p>
                            </div>
                        @endif

                        <x-label for="deliveryBoyId" title="{{ __('Assign New Driver') }}">
                            <livewire:select.edit-order-driver-select name="deliveryBoyId" placeholder="{{ __('Search for driver...') }}"
                                :searchable="true" />
                        </x-label>
                    </div>
                @endcan

                {{-- Status Management --}}
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-md font-semibold text-gray-700 mb-4 flex items-center">
                        <x-tabler-timeline class="w-4 h-4 mr-2 text-primary-500" />
                        {{ __('Order Status') }}
                    </h3>

                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-500 uppercase mb-1">{{ __('Current Status') }}</p>
                        <span class="inline-block px-3 py-1 text-sm rounded-full
                            @if($selectedModel->status == 'delivered') bg-green-100 text-green-800
                            @elseif($selectedModel->status == 'pending') bg-yellow-100 text-yellow-800
                            @elseif($selectedModel->status == 'cancelled') bg-red-100 text-red-800
                            @elseif($selectedModel->status == 'failed') bg-red-100 text-red-800
                            @else bg-primary-100 text-primary-800
                            @endif">
                            {{ ucfirst(__($selectedModel->status)) }}
                        </span>
                    </div>

                    @hasanyrole('admin')
                        <x-select title="{{ __('Change Status To') }}" :options="$this->order_status ?? []" name="status" :defer="false" />
                    @else
                        @can('change-order-status')
                            @if (in_array($selectedModel->status ?? '', ['pending']))
                                <div class="flex gap-4 justify-center">
                                    <button wire:click="rejectOrder"
                                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                        <x-tabler-x class="w-5 h-5 mr-2" />
                                        {{ __('Reject') }}
                                    </button>
                                    <button wire:click="acceptOrder"
                                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                                        <x-tabler-check class="w-5 h-5 mr-2" />
                                        {{ __('Accept') }}
                                    </button>
                                </div>
                            @else
                                <x-select title="{{ __('Change Status To') }}" :options="$this->order_status ?? []" name="status" :defer="false" />
                            @endif
                        @endcan
                    @endhasanyrole
                </div>

                {{-- Payment Status --}}
                @can('order-edit-payment-status')
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-md font-semibold text-gray-700 mb-4 flex items-center">
                            <x-tabler-credit-card class="w-4 h-4 mr-2 text-primary-500" />
                            {{ __('Payment Status') }}
                        </h3>

                        <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500 uppercase mb-1">{{ __('Current Payment Status') }}</p>
                            <span class="inline-block px-3 py-1 text-sm rounded-full
                                @if($selectedModel->payment_status == 'successful') bg-green-100 text-green-800
                                @elseif($selectedModel->payment_status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($selectedModel->payment_status == 'failed') bg-red-100 text-red-800
                                @elseif($selectedModel->payment_status == 'cancelled') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(__($selectedModel->payment_status)) }}
                            </span>
                        </div>

                        <x-select title="{{ __('Change Payment Status To') }}" :options="$this->order_payment_status ?? []" name="paymentStatus" :defer="false" />
                    </div>
                @endcan

                {{-- Reason/Note --}}
                <div class="border-b border-gray-200 pb-6 {{ in_array($status, ['failed', 'cancelled']) || !empty($note) ? 'block' : 'hidden' }}">
                    <h3 class="text-md font-semibold text-gray-700 mb-4 flex items-center">
                        <x-tabler-message class="w-4 h-4 mr-2 text-primary-500" />
                        {{ __('Cancellation/Failure Reason') }}
                    </h3>
                    <x-input title="{{ __('Reason') }}" name="note" placeholder="{{ __('Please provide a reason...') }}" />
                    @if(!empty($selectedModel->reason) && empty($note))
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <p class="text-xs text-gray-500 uppercase mb-1">{{ __('Current Reason') }}</p>
                            <p class="text-sm text-gray-700">{{ $selectedModel->reason }}</p>
                        </div>
                    @endif
                </div>

                {{-- Form Errors --}}
                <x-form-errors />

                {{-- Action Buttons --}}
                <div class="pt-4 flex flex-col md:flex-row gap-4 md:justify-end">
                    <a href="{{ route('orders') }}"
                        class="inline-flex items-center justify-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <x-tabler-x class="w-5 h-5 mr-2" />
                        {{ __('Cancel') }}
                    </a>
                    <button wire:click="update"
                        class="inline-flex items-center justify-center px-6 py-3 bg-primary-500 hover:bg-primary-600 text-theme font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <x-tabler-device-floppy class="w-5 h-5 mr-2" />
                        {{ __('Save Changes') }}
                    </button>
                </div>

            </div>
        </div>

        {{-- Quick Links Card --}}
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <x-tabler-link class="w-5 h-5 mr-2 text-primary-500" />
                {{ __('Quick Actions') }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('orders.details', ['code' => $selectedModel->code]) }}"
                    class="inline-flex items-center justify-center px-4 py-3 bg-primary-500 hover:bg-primary-600 text-theme font-medium rounded-lg shadow-sm transition-colors duration-200">
                    <x-tabler-eye class="w-5 h-5 mr-2" />
                    {{ __('View Order Details') }}
                </a>
                @if($selectedModel->can_edit_products)
                    <a href="{{ route('order.products.edit', ['code' => $selectedModel->code]) }}"
                        class="inline-flex items-center justify-center px-4 py-3 bg-primary-700 hover:bg-primary-900 text-theme font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <x-tabler-shopping-cart class="w-5 h-5 mr-2" />
                        {{ __('Edit Items') }}
                    </a>
                @endif
            </div>
        </div>

    @else
        <div class="p-4 border-2 rounded-xl text-primary-500 border-primary-500 opacity-20 text-center">
            {{ __('No Order Found') }}
        </div>
    @endif

    <x-loading />
</div>
