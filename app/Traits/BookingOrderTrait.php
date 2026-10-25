<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

trait BookingOrderTrait
{
    /**
     * Get the booking order relationship
     * Checks if table exists to prevent errors before migrations are run
     */
    public function booking_order()
    {
        return $this->hasOne('App\Models\BookingOrder', 'order_id', 'id');
    }

    /**
     * Check if this is a booking order
     * Already exists in Order model, but ensuring it works with our new structure
     */
    public function getIsBookingAttribute()
    {
        return ($this->vendor->vendor_type->slug ?? '') == "booking";
    }

    /**
     * Get booking-specific details
     */
    public function getBookingDetailsAttribute()
    {
        if (!$this->is_booking || !$this->booking_order) {
            return null;
        }

        return [
            'property_name' => $this->booking_order->property->name ?? '',
            'check_in' => $this->booking_order->check_in_date,
            'check_out' => $this->booking_order->check_out_date,
            'nights' => $this->booking_order->number_of_nights,
            'guests' => $this->booking_order->number_of_guests,
            'status' => $this->booking_order->is_upcoming ? 'Upcoming' :
                ($this->booking_order->is_current ? 'Current' : 'Past'),
        ];
    }

    /**
     * Get booking property
     */
    public function getBookingPropertyAttribute()
    {
        return $this->booking_order ? $this->booking_order->property : null;
    }



    /**
     * Get booking fees breakdown
     */
    public function getBookingFeesAttribute()
    {
        if (!$this->booking_order) {
            return [];
        }

        return $this->booking_order->fees->map(function ($fee) {
            return [
                'name' => $fee->name,
                'amount' => $fee->calculated_amount,
                'is_taxable' => $fee->is_taxable,
            ];
        });
    }

    /**
     * Check if booking can be cancelled based on cancellation policy
     */
    public function getCanCancelBookingAttribute()
    {
        if (!$this->is_booking || !$this->booking_order) {
            return false;
        }

        return $this->booking_order->can_cancel;
    }

    /**
     * Get potential refund amount if cancelled now
     */
    public function getBookingRefundAmountAttribute()
    {
        if (!$this->is_booking || !$this->booking_order) {
            return 0;
        }

        return $this->booking_order->refund_amount;
    }

    /**
     * Get booking guests
     */
    public function getBookingGuestsAttribute()
    {
        if (!$this->booking_order) {
            return collect([]);
        }

        return $this->booking_order->guests;
    }

    /**
     * Get days until check-in (for upcoming bookings)
     */
    public function getDaysUntilCheckInAttribute()
    {
        if (!$this->booking_order) {
            return null;
        }

        return $this->booking_order->days_until_check_in;
    }

    /**
     * Cancel booking and handle date releases
     */
    public function cancelBooking()
    {
        if (!$this->is_booking || !$this->booking_order) {
            return false;
        }

        // Release blocked dates
        $this->booking_order->releaseDates();

        // Update order status
        $this->setStatus('cancelled');

        return true;
    }

    /**
     * Complete booking (when guest checks out)
     */
    public function completeBooking()
    {
        if (!$this->is_booking || !$this->booking_order) {
            return false;
        }

        // Update order status
        $this->setStatus('delivered'); // Using 'delivered' as 'completed' status

        return true;
    }
}
