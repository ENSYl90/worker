<?php

namespace App\Models;

class PropertyFee extends NoDeleteBaseModel
{
    protected $fillable = [
        'property_id',
        'booking_fee_type_id',
        'amount',
        'min_amount',
        'max_amount',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the property this fee belongs to
     */
    public function property()
    {
        return $this->belongsTo('App\Models\Property', 'property_id', 'id');
    }



    /**
     * Get the fee type
     */
    public function bookingFeeType()
    {
        return $this->belongsTo('App\Models\BookingFeeType', 'booking_fee_type_id', 'id');
    }

    /**
     * Scope to get only active fees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Calculate the actual fee based on booking details
     *
     * @param float $nightsCost
     * @param int $numberOfNights
     * @param int $numberOfGuests
     * @return float
     */
    public function calculateFee($nightsCost, $numberOfNights, $numberOfGuests)
    {
        if (!$this->bookingFeeType) {
            return 0;
        }

        $calculatedFee = 0;

        switch ($this->bookingFeeType->calculation_type) {
            case BookingFeeType::CALCULATION_FIXED:
                $calculatedFee = $this->amount;
                break;

            case BookingFeeType::CALCULATION_PERCENTAGE:
                $calculatedFee = ($nightsCost * $this->amount) / 100;

                // Apply min/max limits
                if ($this->min_amount && $calculatedFee < $this->min_amount) {
                    $calculatedFee = $this->min_amount;
                }
                if ($this->max_amount && $calculatedFee > $this->max_amount) {
                    $calculatedFee = $this->max_amount;
                }
                break;

            case BookingFeeType::CALCULATION_PER_NIGHT:
                $calculatedFee = $this->amount * $numberOfNights;
                break;

            case BookingFeeType::CALCULATION_PER_GUEST:
                $calculatedFee = $this->amount * $numberOfGuests;
                break;
        }

        return round($calculatedFee, 2);
    }

    /**
     * Get a description of how this fee is calculated
     */
    public function getCalculationDescriptionAttribute()
    {
        if (!$this->bookingFeeType) {
            return '';
        }

        switch ($this->bookingFeeType->calculation_type) {
            case BookingFeeType::CALCULATION_FIXED:
                return "Fixed amount: " . setting('currency', '$') . number_format((float) $this->amount, 2);

            case BookingFeeType::CALCULATION_PERCENTAGE:
                $desc = number_format((float) $this->amount, 2) . "% of nights cost";
                if ($this->min_amount) {
                    $desc .= " (min: " . setting('currency', '$') . number_format((float) $this->min_amount, 2) . ")";
                }
                if ($this->max_amount) {
                    $desc .= " (max: " . setting('currency', '$') . number_format((float) $this->max_amount, 2) . ")";
                }
                return $desc;

            case BookingFeeType::CALCULATION_PER_NIGHT:
                return setting('currency', '$') . number_format((float) $this->amount, 2) . " per night";

            case BookingFeeType::CALCULATION_PER_GUEST:
                return setting('currency', '$') . number_format((float) $this->amount, 2) . " per guest";

            default:
                return '';
        }
    }
}
