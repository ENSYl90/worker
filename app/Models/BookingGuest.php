<?php

namespace App\Models;

class BookingGuest extends NoDeleteBaseModel
{
    protected $fillable = [
        'booking_order_id',
        'guest_type',
        'first_name',
        'last_name',
        'age',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    protected $appends = ['full_name'];

    /**
     * Get the booking order this guest belongs to
     */
    public function bookingOrder()
    {
        return $this->belongsTo('App\Models\BookingOrder', 'booking_order_id', 'id');
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Scope to get only adults
     */
    public function scopeAdults($query)
    {
        return $query->where('guest_type', 'adult');
    }

    /**
     * Scope to get only children
     */
    public function scopeChildren($query)
    {
        return $query->where('guest_type', 'child');
    }
}
