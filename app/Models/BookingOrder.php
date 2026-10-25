<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class BookingOrder extends BaseModel
{
    use SoftDeletes;

    //ENUM: Allowed statuses for booking order, pending, confirmed, checkin, checkout, cancelled, completed, expired
    const PENDING = 'pending';
    const CONFIRMED = 'confirmed';
    const AWAITING_HOST_APPROVAL = 'awaiting_host_approval';
    const CHECKIN = 'checkin';
    const CHECKOUT = 'checkout';
    const CANCELLED = 'cancelled';
    const COMPLETED = 'delivered';
    const EXPIRED = 'expired';

    protected $fillable = [
        'order_id',
        'property_id',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'number_of_guests',
        'number_of_adults',
        'number_of_children',
        'number_of_infants',
        'number_of_pets',
        'number_of_nights',
        'base_price',
        'total_nights_cost',
        'booking_type',
        'special_requests',
        'cancellation_policy_id',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'number_of_guests' => 'integer',
        'number_of_adults' => 'integer',
        'number_of_children' => 'integer',
        'number_of_infants' => 'integer',
        'number_of_pets' => 'integer',
        'number_of_nights' => 'integer',
        'base_price' => 'decimal:2',
        'total_nights_cost' => 'decimal:2',
    ];

    protected $appends = ['can_cancel', 'refund_amount', 'total_fees'];
    protected $with = ["fees"];

    /**
     * Get the main order
     */
    public function order()
    {
        return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    /**
     * Get the property
     */
    public function property()
    {
        return $this->belongsTo('App\Models\Property', 'property_id', 'id');
    }



    /**
     * Get the cancellation policy
     */
    public function cancellationPolicy()
    {
        return $this->belongsTo('App\Models\CancellationPolicy', 'cancellation_policy_id', 'id');
    }

    /**
     * Get guests for this booking
     */
    public function guests()
    {
        return $this->hasMany('App\Models\BookingGuest', 'booking_order_id', 'id');
    }

    /**
     * Get fees for this booking
     */
    public function fees()
    {
        return $this->hasMany('App\Models\BookingOrderFee', 'booking_order_id', 'id');
    }

    /**
     * Calculate total fees
     */
    public function getTotalFeesAttribute()
    {
        return $this->fees()->sum('calculated_amount');
    }

    /**
     * Calculate grand total (nights + fees)
     */
    public function getGrandTotalAttribute()
    {
        return $this->total_nights_cost + $this->total_fees;
    }

    /**
     * Check if booking can be cancelled
     */
    public function getCanCancelAttribute()
    {
        if (!$this->relationLoaded('order') || !$this->order) {
            return false;
        }

        $preventOrderCancellation = setting('finance.preventOrderCancellation', "");
        if (!is_array($preventOrderCancellation)) {
            $preventOrderCancellation = json_decode($preventOrderCancellation, true) ?? [];
        }

        return !in_array($this->order->status, $preventOrderCancellation);
    }

    /**
     * Calculate refund amount if cancelled now
     */
    public function getRefundAmountAttribute()
    {
        if (!$this->relationLoaded('order') || !$this->cancellationPolicy || !$this->order) {
            return 0;
        }

        return $this->cancellationPolicy->calculateRefund($this);
    }



    /**
     * Block dates for this booking
     *
     * @return void
     */
    public function blockDates()
    {
        $dates = $this->getDateRange();

        PropertyAvailability::blockDates(
            $this->property_id,
            $dates,
            'booked',
            $this->id
        );

        // Also block preparation days after checkout
        if ($this->property && $this->property->preparation_time > 0) {
            $preparationDates = $this->getPreparationDates();
            PropertyAvailability::blockDates(
                $this->property_id,
                $preparationDates,
                'maintenance',
                null
            );
        }
    }

    /**
     * Release/unblock dates for this booking
     *
     * @return void
     */
    public function releaseDates()
    {
        $dates = $this->getDateRange();

        PropertyAvailability::releaseDates(
            $this->property_id,
            $dates
        );

        // Also release preparation days
        if ($this->property && $this->property->preparation_time > 0) {
            $preparationDates = $this->getPreparationDates();
            PropertyAvailability::releaseDates(
                $this->property_id,
                $preparationDates
            );
        }
    }

    /**
     * Get array of dates between check-in and check-out
     *
     * @return array
     */
    public function getDateRange()
    {
        $dates = [];
        $current = Carbon::parse($this->check_in_date);
        $end = Carbon::parse($this->check_out_date);

        while ($current->lt($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Get preparation dates after checkout
     *
     * @return array
     */
    protected function getPreparationDates()
    {
        $dates = [];
        $property = $this->property;

        if (!$property || $property->preparation_time <= 0) {
            return $dates;
        }

        $current = Carbon::parse($this->check_out_date);
        $end = $current->copy()->addDays($property->preparation_time);

        while ($current->lt($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Check if booking is upcoming (check-in is in the future)
     */
    public function getIsUpcomingAttribute()
    {
        return Carbon::parse($this->check_in_date)->isFuture();
    }

    /**
     * Check if booking is current (between check-in and check-out)
     */
    public function getIsCurrentAttribute()
    {
        $now = Carbon::now();
        $checkIn = Carbon::parse($this->check_in_date);
        $checkOut = Carbon::parse($this->check_out_date);

        return $now->between($checkIn, $checkOut);
    }

    /**
     * Check if booking is past (check-out has passed)
     */
    public function getIsPastAttribute()
    {
        return Carbon::parse($this->check_out_date)->isPast();
    }

    /**
     * Get days until check-in
     */
    public function getDaysUntilCheckInAttribute()
    {
        $checkIn = Carbon::parse($this->check_in_date);
        return now()->diffInDays($checkIn, false);
    }
}
