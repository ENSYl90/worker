<table class="w-full border rounded">
    <thead>
        <tr class="font-medium bg-gray-200 ">
            <th class="p-2 w-6/12 text-left">{{ __('Product') }}</th>
            <th class="p-2 text-left">{{ __('Options') }}</th>
            <th class="p-2 text-left">{{ __('Price') }}</th>
            <th class="p-2 text-center">{{ __('Quantity') }}</th>
        </tr>
    </thead>
    <tbody>

        @if (!empty($products))
            @foreach ($products as $product)
                <tr class="font-light border-b ">
                    <td class="p-2 text-sm w-6/12">
                        <p class="font-medium text-gray-800">{{ $product->product->name }}</p>
                        <p class="font-thin text-gray-500 text-xs">
                            <span class="font-medium">{{ __("Brand") }}:</span>
                            {{ $product->product->brand->name ?? 'N/A' }}
                        </p>

                    </td>
                    <td class="p-2 text-sm">{{ $product->options }}</td>
                    <td class="p-2 text-sm">{{ currencyFormat($product->price) }}</td>
                    <td class="p-2 text-sm text-center">{{ $product->quantity }}</td>
                </tr>
            @endforeach
        @endif

    </tbody>
</table>