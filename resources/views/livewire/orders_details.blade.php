@section('title', __('Order Details') . " #" . ($selectedModel->code ?? "404"))
<div class="px-4">
    @php
        $goBackLink = $selectedModel->taxi_order != null ? route('orders.taxi') : route('orders');
    @endphp

    {{-- Header with Back Button --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ __('Order Details') }}</h1>
        <div class="flex gap-3">
            @if(!empty($selectedModel))
                @can('edit-orders')
                    <a href="{{ route('order.edit', ['code' => $selectedModel->code]) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <x-tabler-edit class="w-4 h-4 mr-2" />
                        {{ __('Edit Order') }}
                    </a>
                @endcan
            @endif
            <a href="{{ $goBackLink }}"
                class="inline-flex items-center px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg shadow-sm transition-colors duration-200">
                <x-tabler-arrow-left class="w-4 h-4 mr-2" />
                {{ __('Back to Orders') }}
            </a>
        </div>
    </div>

    @empty($selectedModel)
        <div class="p-4 border-2 rounded-xl text-primary-500 border-primary-500 opacity-20 text-center">
            {{ __('No Order Found') }}
        </div>
    @else
        {{-- Modern Unified Order Details --}}
        @include('livewire.order.unified_order_details')
    @endempty

    {{-- Loading Component --}}
    <x-loading />
</div>