@if (!empty($order->fees))
    @php

        $fees = json_decode($order->fees, true);
        if (!is_array($fees)) {
            $fees = json_decode($fees, true);
        }
    @endphp
    @php
        $orderSummaryBreakdown = [];
        foreach ($fees ?? [] as $fee) {
            $orderSummaryBreakdown[] = [
                'key' => __($fee['name']),
                'value' => '+' . currencyFormat($fee['amount'] ?? ''),
            ];
        }
    @endphp
    @include('livewire.order.order-summary', ['breakdown' => $orderSummaryBreakdown])
@endif
