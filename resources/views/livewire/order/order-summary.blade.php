<div class="bg-white shadow-sm border border-gray-200 overflow-hidden rounded-lg">
    <table class="w-full">
        <tbody>
            @foreach ($breakdown as $index => $item)
                @php
                    $isTotal = isset($item['is_total']) && $item['is_total'];
                    $isDiscount = isset($item['is_discount']) && $item['is_discount'];
                    $isFee = isset($item['is_fee']) && $item['is_fee'];
                @endphp
                <tr class="flex items-center border-b border-gray-100 last:border-b-0
                    {{ $isTotal ? 'border-t-2 border-t-gray-200 bg-gray-50' : '' }}">
                    <td class="flex-1 text-left text-sm py-2.5 px-4
                        {{ $isTotal ? 'font-semibold text-gray-800' : 'text-gray-600' }}">
                        {{ $item['key'] }}
                    </td>
                    <td class="text-right text-sm py-2.5 px-4 tabular-nums
                        {{ $isTotal ? 'font-bold text-gray-900' : 'font-medium text-gray-700' }}
                        {{ $isDiscount ? 'text-red-500' : '' }}
                        {{ $isFee ? 'text-primary-600' : '' }}">
                        {{ $item['value'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
