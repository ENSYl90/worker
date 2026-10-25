@if ($model->type === \App\Models\DriverSubscription::TYPE_TIME)
    {{ max(now()->diffInDays($model->expires_at, false), 0) }} {{ __('days') }}
@elseif ($model->type === \App\Models\DriverSubscription::TYPE_ORDERS)
    {{ $model->remaining_orders ?? 0 }} {{ __('orders') }}
@else
    {{ __('N/A') }}
@endif
