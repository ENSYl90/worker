<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasTranslations;

class BookingFeeType extends BaseModel
{
    use SoftDeletes, HasTranslations;
    public $translatable = ['name', "description"];

    // Calculation type constants
    const CALCULATION_FIXED = 'fixed';           // e.g., $50
    const CALCULATION_PERCENTAGE = 'percentage'; // e.g., 10% of nights_cost
    const CALCULATION_PER_NIGHT = 'per_night';   // e.g., $10 per night
    const CALCULATION_PER_GUEST = 'per_guest';   // e.g., $5 per guest

    protected $fillable = [
        'name',
        'slug',
        'description',
        'calculation_type',
        'is_mandatory',
        'is_taxable',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_taxable' => 'boolean',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];


    public static function boot()
    {
        parent::boot();

        //while creating/inserting item into db
        static::creating(function ($model) {
            $model->slug = \Str::slug($model->name);
        });
        static::updating(function ($model) {
            $model->slug = \Str::slug($model->name);
        });
    }

    /**
     * Get property fees using this fee type
     */
    public function propertyFees()
    {
        return $this->hasMany('App\Models\PropertyFee', 'booking_fee_type_id', 'id');
    }

    /**
     * Get booking order fees using this fee type
     */
    public function bookingOrderFees()
    {
        return $this->hasMany('App\Models\BookingOrderFee', 'booking_fee_type_id', 'id');
    }

    /**
     * Scope to get only active fee types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope to get only mandatory fee types
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', 1);
    }

    /**
     * Scope ordered by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Get all calculation types
     */
    public static function getCalculationTypes()
    {
        return [
            self::CALCULATION_FIXED => __('Fixed Amount'),
            self::CALCULATION_PERCENTAGE => __('Percentage of Nights Cost'),
            self::CALCULATION_PER_NIGHT => __('Per Night'),
            self::CALCULATION_PER_GUEST => __('Per Guest'),
        ];
    }
}
