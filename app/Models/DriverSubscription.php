<?php

namespace App\Models;

class DriverSubscription extends NoDeleteBaseModel
{
    const TYPE_ORDERS = 'orders';
    const TYPE_TIME = 'time';

    protected $casts = [
        'id' => 'integer',
        'days' => 'integer',
        'order_limit' => 'integer',
        'amount' => 'double',
        'is_active' => 'boolean',
    ];

    public function histories()
    {
        return $this->hasMany(DriverSubscriptionHistory::class);
    }
}
