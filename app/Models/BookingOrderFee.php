<?php

namespace App\Models;

class BookingOrderFee extends NoDeleteBaseModel
{
    protected $fillable = [
        'booking_order_id',
        'booking_fee_type_id',
        'name',
        'calculation_type',
        'base_amount',
        'calculated_amount',
        'is_taxable',
    ];

    protected $casts = [
        'base_amount' => 'float',
        'calculated_amount' => 'float',
        'is_taxable' => 'boolean',
    ];

    protected $appends = [];
    protected $hidden = ["created_at", "updated_at"];

    /**
     * Get the booking order this fee belongs to
     */
    public function bookingOrder()
    {
        return $this->belongsTo('App\Models\BookingOrder', 'booking_order_id', 'id');
    }

    /**
     * Get the fee type
     */
    public function bookingFeeType()
    {
        return $this->belongsTo('App\Models\BookingFeeType', 'booking_fee_type_id', 'id');
    }

    /**
     * Scope to get only taxable fees
     */
    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', 1);
    }

    /**
     * Scope to get non-taxable fees
     */
    public function scopeNonTaxable($query)
    {
        return $query->where('is_taxable', 0);
    }

    /**
     * Get a description of this fee for display
     */
    public function getDescriptionAttribute()
    {
        $desc = $this->name . ': ' . setting('currency', '$') . number_format($this->calculated_amount, 2);

        if ($this->is_taxable) {
            $desc .= ' (taxable)';
        }

        return $desc;
    }

    /**
     * Check if this fee is refundable on cancellation
     * By default, all fees are refundable based on cancellation policy
     * Override this method if certain fees should never be refunded
     */
    public function getIsRefundableAttribute()
    {
        // You can customize this based on fee type
        // For example, service fees might not be refundable
        return true;
    }
}
