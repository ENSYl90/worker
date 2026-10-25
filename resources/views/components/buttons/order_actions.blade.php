{{-- <div class="grid grid-cols-2 md:grid-cols-3 gap-2 w-20 md:w-32"> --}}
    <div class="flex flex-wrap gap-2">

        @can('view-order-chat')
            @php
                $chatUrl = route('order.chats', $model->code);
            @endphp
            <x-buttons.plain wireClick="$emit('newTab', '{{ $chatUrl }}')">
                <x-lineawesome-comments-solid class="w-5 h-5" />
            </x-buttons.plain>
        @endcan
        @can('view-print-order')
            @php
                $printUrl = route('order.print', $model->code);
            @endphp
            <x-buttons.plain bgColor="bg-black" wireClick="$emit('newTab', '{{ $printUrl }}' )">
                <x-lineawesome-print-solid class="w-5 h-5" />
            </x-buttons.plain>
        @endcan
        @php
            if (($model->is_booking ?? false)) {
                $orderDetailsUrl = route("booking.order.details", $model->code);
            } else {
                $orderDetailsUrl = route("orders.details", $model->code);
            }
        @endphp
        <x-buttons.custom_link :link="$orderDetailsUrl" newTab="false"
            bg="bg-yellow-600 active:bg-yellow-600 hover:bg-yellow-700">
            <x-heroicon-o-eye class="w-5 h-5" />
        </x-buttons.custom_link>

        @if (canI('edit-order') && !($model->is_booking ?? false))
            @php
                $orderEditDetailsUrl = route("order.edit", $model->code);
                $orderStatusNotDone = !in_array($model->status, ['failed', 'delivered', 'cancelled']);
                $noPaymentYet = empty($model->payment);
                $paymentNotInReview = !in_array($model->payment_status, ['review']);
            @endphp
            @if ($orderStatusNotDone && ($paymentNotInReview || $noPaymentYet))

                <x-buttons.custom_link :link="$orderEditDetailsUrl" newTab="false"
                    bg="bg-gray-600 active:bg-gray-600 hover:bg-gray-700">
                    <x-tabler-edit class="w-5 h-5" />
                </x-buttons.custom_link>
            @endif
        @endif
        @if (canI('review-order-payment') && !($model->is_booking ?? false))
            @if (in_array($model->payment_status, ['review']) && !empty($model->payment))
                <x-buttons.review :model="$model" />
            @endif
        @endif
    </div>