<?php

namespace App\Models;

use Carbon\Carbon;

class DriverSubscriptionHistory extends NoDeleteBaseModel
{
    protected $table = 'driver_subscription_histories';

    protected $casts = [
        'id' => 'integer',
        'driver_subscription_id' => 'integer',
        'driver_id' => 'integer',
        'wallet_transaction_id' => 'integer',
        'amount' => 'double',
        'order_limit' => 'integer',
        'remaining_orders' => 'integer',
        'completed_orders' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'successful')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })
            ->where(function ($query) {
                $query->whereNull('remaining_orders')
                    ->orWhere('remaining_orders', '>', 0);
            });
    }

    //expired: negative of active scope
    public function scopeExpired($query)
    {
        return $query->where('status', 'successful')
            ->where(function ($query) {
                $query->whereNotNull('expires_at')->where('expires_at', '<', Carbon::now());
            })
            ->Orwhere(function ($query) {
                $query->whereNotNull('remaining_orders')->where('remaining_orders', '<', 0);
            });
    }

    public function subscription()
    {
        return $this->belongsTo(DriverSubscription::class, 'driver_subscription_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id')->withTrashed();
    }

    public function wallet_transaction()
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id', 'id');
    }
}
