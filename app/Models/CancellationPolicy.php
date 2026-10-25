<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTranslations;

class CancellationPolicy extends BaseModel
{
    use SoftDeletes;
    use HasTranslations;
    public $translatable = ['name', "description"];
    protected $fillable = [
        'name',
        'description',
        'refund_percentage_rules',
        'is_active',
    ];

    protected $casts = [
        'refund_percentage_rules' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get properties using this cancellation policy
     */
    public function properties()
    {
        return $this->hasMany('App\Models\Property', 'cancellation_policy_id', 'id');
    }

    /**
     * Get booking orders using this cancellation policy
     */
    public function bookingOrders()
    {
        return $this->hasMany('App\Models\BookingOrder', 'cancellation_policy_id', 'id');
    }

    /**
     * Scope to get only active policies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Calculate refund amount based on cancellation date
     *
     * @param BookingOrder $bookingOrder
     * @param \Carbon\Carbon|null $cancellationDate
     * @return float
     */
    public function calculateRefund($bookingOrder, $cancellationDate = null)
    {
        $cancellationDate = $cancellationDate ?? now();
        $checkInDate = \Carbon\Carbon::parse($bookingOrder->check_in_date);
        $daysUntilCheckIn = $cancellationDate->diffInDays($checkInDate, false);

        // If already past check-in date, no refund
        if ($daysUntilCheckIn < 0) {
            return 0;
        }

        $refundPercentage = 0;
        $rules = $this->refund_percentage_rules ?? [];

        // Sort rules by days_before descending
        usort($rules, function ($a, $b) {
            return ($b['days_before'] ?? 0) - ($a['days_before'] ?? 0);
        });

        // Find applicable refund percentage
        foreach ($rules as $rule) {
            if ($daysUntilCheckIn >= ($rule['days_before'] ?? 0)) {
                $refundPercentage = $rule['refund_percentage'] ?? 0;
                break;
            }
        }

        // Calculate refund amount from order total
        $orderTotal = $bookingOrder->order->total ?? 0;
        return ($orderTotal * $refundPercentage) / 100;
    }

    /**
     * Get a human-readable description of the policy
     */
    public function getDescriptionTextAttribute()
    {
        $rules = $this->refund_percentage_rules ?? [];
        $descriptions = [];

        foreach ($rules as $rule) {
            $days = $rule['days_before'] ?? 0;
            $percentage = $rule['refund_percentage'] ?? 0;

            if ($days == 0) {
                $descriptions[] = "Less than 1 day before check-in: {$percentage}% refund";
            } else {
                $descriptions[] = "{$days}+ days before check-in: {$percentage}% refund";
            }
        }

        return implode(' | ', $descriptions);
    }
}
