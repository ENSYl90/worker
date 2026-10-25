<?php

namespace App\Models;

class PropertyAvailability extends NoDeleteBaseModel
{
    protected $fillable = [
        'property_id',
        'date',
        'is_available',
        'price_override',
        'reason',
        'booking_order_id',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
        'price_override' => 'decimal:2',
    ];

    /**
     * Get the property this availability belongs to
     */
    public function property()
    {
        return $this->belongsTo('App\Models\Property', 'property_id', 'id');
    }



    /**
     * Get the booking order that made this date unavailable
     */
    public function bookingOrder()
    {
        return $this->belongsTo('App\Models\BookingOrder', 'booking_order_id', 'id');
    }

    /**
     * Scope to get availability for a date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get only available dates
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', 1);
    }

    /**
     * Scope to get only booked dates
     */
    public function scopeBooked($query)
    {
        return $query->where('is_available', 0)->where('reason', 'booked');
    }

    /**
     * Scope to get blocked dates
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_available', 0)->where('reason', 'blocked');
    }

    /**
     * Mark this date as booked
     *
     * @param int $bookingOrderId
     * @return void
     */
    public function markAsBooked($bookingOrderId)
    {
        $this->is_available = false;
        $this->reason = 'booked';
        $this->booking_order_id = $bookingOrderId;
        $this->save();
    }

    /**
     * Mark this date as available
     *
     * @return void
     */
    public function markAsAvailable()
    {
        $this->is_available = true;
        $this->reason = 'available';
        $this->booking_order_id = null;
        $this->save();
    }

    /**
     * Mark this date as blocked by host
     *
     * @return void
     */
    public function markAsBlocked()
    {
        $this->is_available = false;
        $this->reason = 'blocked';
        $this->booking_order_id = null;
        $this->save();
    }

    /**
     * Block multiple dates for a property
     *
     * @param int $propertyId
     * @param array $dates
     * @param string $reason
     * @param int|null $bookingOrderId
     * @return void
     */
    public static function blockDates($propertyId, $dates, $reason = 'booked', $bookingOrderId = null)
    {
        foreach ($dates as $date) {
            self::updateOrCreate(
                [
                    'property_id' => $propertyId,
                    'date' => $date,
                ],
                [
                    'is_available' => false,
                    'reason' => $reason,
                    'booking_order_id' => $bookingOrderId,
                ]
            );
        }
    }

    /**
     * Release/unblock multiple dates
     *
     * @param int $propertyId
     * @param array $dates
     * @return void
     */
    public static function releaseDates($propertyId, $dates)
    {
        self::where('property_id', $propertyId)
            ->whereIn('date', $dates)
            ->update([
                'is_available' => true,
                'reason' => 'available',
                'booking_order_id' => null,
            ]);
    }
}
