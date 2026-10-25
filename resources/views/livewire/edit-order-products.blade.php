@php
    $pageTitle = __('Edit Order') . ' #';
    if ($selectedModel != null) {
        $pageTitle .= $selectedModel->code;
    }
    $pageTitle .= ' ';
    $pageTitle .= __('Items');
@endphp
@section('title', $pageTitle)
<div class="px-4">

    {{-- Header with Back Button --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ $pageTitle }}</h1>
        <a href="{{ route('orders') }}"
            class="inline-flex items-center px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
            <x-tabler-arrow-left class="w-4 h-4 mr-2" />
            {{ __('Back to Orders') }}
        </a>
    </div>

    {{-- Order Info Card --}}
    @if($selectedModel != null)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Order Code') }}</p>
                    <p class="text-lg font-semibold text-gray-800">#{{ $selectedModel->code }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ __('Status') }}</p>
                    <p class="text-lg font-semibold">
                        <span
                            class="px-3 py-1 text-sm rounded-full
                                                                                                                        @if($selectedModel->status == 'delivered') bg-green-100 text-green-800
                                                                                                                        @elseif($selectedModel->status == 'pending') bg-yellow-100 text-yellow-800
                                                                                                                        @elseif($selectedModel->status == 'cancelled') bg-red-100 text-red-800
                                                                                                                        @else bg-primary-100 text-theme
                                                                                                                        @endif">
                            {{ ucfirst(__($selectedModel->status)) }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ __('Payment Status') }}</p>
                    <p class="text-lg font-semibold">
                        <span
                            class="px-3 py-1 text-sm rounded-full
                                                                                                                        @if($selectedModel->payment_status == 'successful') bg-green-100 text-green-800
                                                                                                                        @elseif($selectedModel->payment_status == 'pending') bg-yellow-100 text-yellow-800
                                                                                                                        @elseif($selectedModel->payment_status == 'failed') bg-red-100 text-red-800
                                                                                                                        @else bg-gray-100 text-gray-800
                                                                                                                        @endif">
                            {{ ucfirst(__($selectedModel->payment_status)) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Edit Card --}}
    <div class="bg-white rounded-lg shadow-md p-6">

        {{-- Item Search --}}
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <x-tabler-search class="w-5 h-5 mr-2 text-primary-500" />
                {{ __('Add Items') }}
            </h2>
            <livewire:component.autocomplete-input title="{{ __('Item') }}" column="name" model="Product"
                emitFunction="autocompleteProductSelected" updateQueryClauseName="productQueryClasueUpdate"
                :clear="true" :queryClause="$productSearchClause" onclearCalled="clearAutocompleteFieldsEvent" />
        </div>

        {{-- Items Table --}}
        <div class="overflow-x-auto">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <x-tabler-shopping-cart class="w-5 h-5 mr-2 text-primary-500" />
                {{ __('Order Items') }}
            </h2>
            <table class="w-full border border-gray-200 rounded-lg overflow-clip">
                <thead>
                    <tr class="bg-primary-500">
                        <th class="text-theme text-left w-1/12 p-3 border-b border-primary-600">{{ __('S/N') }}</th>
                        <th class="text-theme text-left w-3/12 p-3 border-b border-primary-600">{{ __('Name') }}</th>
                        <th class="text-theme text-left w-3/12 p-3 border-b border-primary-600">{{ __('Options') }}</th>
                        <th class="text-theme text-left w-2/12 p-3 border-b border-primary-600">{{ __('Price') }}</th>
                        <th class="text-theme text-left w-2/12 p-3 border-b border-primary-600">{{ __('QTY') }}</th>
                        <th class="text-theme text-left w-2/12 p-3 border-b border-primary-600">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($editOrderProducts ?? [] as $key => $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-4 border-b border-gray-200">
                                <span class="font-medium text-gray-700">{{ $key + 1 }}</span>
                            </td>
                            <td class="w-auto px-3 py-4 border-b border-gray-200">
                                <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                <p class="font-thin text-gray-500 text-xs">
                                    <span class="font-medium">{{ __("Brand") }}:</span>
                                    {{ $product->brand->name ?? 'N/A' }}
                                </p>
                            </td>
                            <td class="w-4/12 px-3 py-4 border-b border-gray-200">
                                @foreach ($product->option_groups as $option_group)
                                    <div class="p-2 mb-2 bg-gray-50 rounded border border-gray-200">
                                        <p class="text-sm font-semibold text-gray-700 mb-2">{{ $option_group->name }}</p>
                                        <div class="ml-2 space-y-1">
                                            @foreach ($option_group->options as $option)
                                                @php
                                                    if ($option_group->multiple) {
                                                        $optionId =
                                                            'editProductOptions.' .
                                                            $product->id .
                                                            '.' .
                                                            $option_group->id .
                                                            '.' .
                                                            $option->id;
                                                    } else {
                                                        $optionId =
                                                            'editProductOptions.' .
                                                            $product->id .
                                                            '.' .
                                                            $option_group->id;
                                                    }
                                                @endphp
                                                @if ($option_group->multiple)
                                                    <x-checkbox
                                                        title="{{ $option->name . ' (' . currencyFormat($option->price) . ')' }}"
                                                        name="{{ $optionId }}" :defer="false" />
                                                @else
                                                    <x-radio type="radio"
                                                        title="{{ $option->name . ' (' . currencyFormat($option->price) . ')' }}"
                                                        name="{{ $optionId }}" :defer="false" value="{{ $option->id }}" />
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </td>
                            <td class="px-3 py-4 border-b border-gray-200">
                                <span class="font-medium text-gray-700">{{ currencyFormat($product->sell_price) }}</span>
                            </td>

                            <td class="w-1/12 px-3 py-4 border-b border-gray-200">
                                <x-input name="editOrderProductsQtys.{{ $product->id }}" type="number" />
                            </td>
                            <td class="w-2/12 px-3 py-4 border-b border-gray-200">
                                <button wire:click="$emit('removeProductFromOrder', '{{ $product->id }}' )"
                                    class="inline-flex items-center px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                                    <x-tabler-trash class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-gray-500">
                                <x-tabler-shopping-cart-off class="w-12 h-12 mx-auto mb-2 text-gray-400" />
                                <p>{{ __('No items added yet. Search and add items above.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Form Errors --}}
        <div class="mt-4">
            <x-form-errors />
        </div>

        {{-- Action Buttons --}}
        <div class="mt-6 flex flex-col md:flex-row gap-4 md:justify-end">
            @if (
                    $selectedModel != null &&
                    $selectedModel->vendor != null &&
                    $selectedModel->vendor->vendor_type != null &&
                    $selectedModel->vendor->vendor_type->slug == 'pharmacy' &&
                    !in_array($selectedModel->payment_status, ['request', 'failed', 'cancelled', 'successful'])
                )
                <button wire:click="requestPayment"
                    class="inline-flex items-center justify-center px-6 py-3 bg-primary-500 hover:bg-primary-600 text-theme font-medium rounded-lg shadow-sm transition-colors duration-200">
                    <x-tabler-credit-card class="w-5 h-5 mr-2" />
                    {{ __('Request Payment') }}
                </button>
            @endif
            <button wire:click="updateOrderProducts"
                class="inline-flex items-center justify-center px-6 py-3 bg-primary-500 hover:bg-primary-600 text-theme font-medium rounded-lg shadow-sm transition-colors duration-200">
                <x-tabler-check class="w-5 h-5 mr-2" />
                {{ __('Save Changes') }}
            </button>
        </div>
    </div>

    <x-page-loader target="requestPayment" />
    <x-page-loader target="updateOrderProducts" />
</div>