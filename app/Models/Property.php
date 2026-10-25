<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Traits\HasTranslations;
use WillVincent\Rateable\Rateable;

class Property extends BaseModel implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, HasTranslations, Rateable;
    public $translatable = ['name', "description"];
    protected $fillable = [
        'vendor_id',
        'property_type_id',
        'base_price',
        'discount_price',
        'name',
        'slug',
        'description',
        'address',
        'latitude',
        'longitude',
        'city',
        'state',
        'country',
        'postal_code',
        'check_in_time_start',
        'check_in_time_end',
        'check_out_time',
        'max_guests',
        'bedrooms',
        'bathrooms',
        'beds',
        'square_meters',
        'is_active',
        'is_instant_booking',
        'minimum_nights',
        'maximum_nights',
        'preparation_time',
        'cancellation_policy_id',
        'house_rules',
        'is_host_residing',
        'featured',
    ];

    protected $casts = [
        'base_price' => 'double',
        'discount_price' => 'double',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'is_instant_booking' => 'boolean',
        'is_host_residing' => 'boolean',
        'max_guests' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'beds' => 'integer',
        'square_meters' => 'integer',
        'minimum_nights' => 'integer',
        'maximum_nights' => 'integer',
        'preparation_time' => 'integer',
    ];

    protected $appends = ['photos', 'main_photo', 'rating', 'user_rated', 'is_favourite', 'shareable_link', 'deep_link'];
    protected $withCount = ['reviews'];

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
     * Get favourites for this property
     */
    public function favourites()
    {
        return $this->hasMany('App\Models\Favourite', 'property_id', 'id');
    }

    /**
     * Check if property is favourited by current user
     */
    public function getIsFavouriteAttribute()
    {
        $userId = auth('api')->id() ?? auth()->id();
        if ($userId) {
            return $this->favourites()->where('user_id', $userId)->exists();
        }
        return false;
    }

    /**
     * Get the vendor/host that owns this property
     */
    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor', 'vendor_id', 'id')->withTrashed();
    }

    /**
     * Get the property type assigned to this property
     */
    public function propertyType()
    {
        return $this->belongsTo('App\Models\PropertyType', 'property_type_id', 'id');
    }

    /**
     * Get the reviews for this property
     */
    public function propertyReviews()
    {
        return $this->hasMany('App\Models\PropertyReview', 'property_id', 'id');
    }

    /**
     * Get booking orders for this property
     */
    public function bookingOrders()
    {
        return $this->hasMany('App\Models\BookingOrder', 'property_id', 'id');
    }

    /**
     * Get amenities for this property
     */
    public function amenities()
    {
        return $this->belongsToMany('App\Models\Amenity', 'amenity_property', 'property_id', 'amenity_id')
            ->withTimestamps();
    }

    /**
     * Get availability records for this property
     */
    public function availabilities()
    {
        return $this->hasMany('App\Models\PropertyAvailability', 'property_id', 'id');
    }

    /**
     * Get fees configured for this property
     */
    public function fees()
    {
        return $this->hasMany('App\Models\PropertyFee', 'property_id', 'id');
    }

    /**
     * Get active fees
     */
    public function activeFees()
    {
        return $this->hasMany('App\Models\PropertyFee', 'property_id', 'id')->where('is_active', 1);
    }

    /**
     * Get cancellation policy
     */
    public function cancellationPolicy()
    {
        return $this->belongsTo('App\Models\CancellationPolicy', 'cancellation_policy_id', 'id');
    }

    /**
     * Get the mine if role is not admin, 
     */
    public function scopeMine($query)
    {
        if (auth()->user()->hasRole('admin')) {
            return $query;
        }
        return $query->where('vendor_id', auth()->user()->id);
    }

    /**
     * Scope to get only active properties
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope to filter by property type
     */
    public function scopeByType($query, $typeId)
    {
        return $query->where('property_type_id', $typeId);
    }

    /**
     * Scope to search within location radius
     */
    /**
     * Scope to search within location radius
     */
    public function scopeWithinLocation($query, $latitude, $longitude, $radiusKm = 50)
    {
        return $query->sortByDistance($latitude, $longitude)
            ->having('distance', '<', $radiusKm);
    }

    /**
     * Scope to sort by distance from coordinates
     */
    public function scopeSortByDistance($query, $latitude, $longitude)
    {
        if (is_null($query->getQuery()->columns)) {
            $query->select('properties.*');
        }

        return $query->selectRaw("( 6371 * acos( cos( radians(?) ) *
            cos( radians( latitude ) ) *
            cos( radians( longitude ) - radians(?) ) +
            sin( radians(?) ) *
            sin( radians( latitude ) ) ) ) AS distance",
            [$latitude, $longitude, $latitude]
        )
            ->orderBy('distance');
    }

    /**
     * Scope to filter available properties for a given date range
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $checkIn
     * @param string $checkOut
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query, $checkIn, $checkOut)
    {
        return $query->whereDoesntHave('availabilities', function ($q) use ($checkIn, $checkOut) {
            $q->where('is_available', 0)
                ->whereDate('date', '>=', $checkIn)
                ->whereDate('date', '<', $checkOut);
        });
    }

    /**
     * Check if property is available for given dates
     *
     * @param string $checkIn
     * @param string $checkOut
     * @return bool
     */
    public function isAvailable($checkIn, $checkOut)
    {
        $dates = $this->getDateRange($checkIn, $checkOut);

        $unavailableDates = PropertyAvailability::where('property_id', $this->id)
            ->whereIn('date', $dates)
            ->where('is_available', 0)
            ->count();

        return $unavailableDates === 0;
    }

    /**
     * Get average rating attribute
     */
    public function getRatingAttribute()
    {
        return (float) $this->reviews()->avg('rating');
    }

    /**
     * Check if current user has rated this property
     */
    public function getUserRatedAttribute()
    {
        return $this->ratings()->where('user_id', auth('api')->id() ?? auth()->id())->exists();
    }

    /**
     * Get the total number of ratings/reviews
     */
    public function reviews()
    {
        return $this->hasMany('App\Models\PropertyReview', 'property_id', 'id');
    }

    public function getPricingForDates($checkIn, $checkOut)
    {
        $dates = $this->getDateRange($checkIn, $checkOut);
        $numberOfNights = count($dates);

        // Get base price per night
        $basePrice = $this->discount_price > 0 ? $this->discount_price : $this->base_price;

        // Check for price overrides
        $totalNightsCost = 0;
        foreach ($dates as $date) {
            $availability = PropertyAvailability::where('property_id', $this->id)
                ->where('date', $date)
                ->first();

            $nightPrice = $availability && $availability->price_override
                ? $availability->price_override
                : $basePrice;

            $totalNightsCost += $nightPrice;
        }

        return [
            'base_price' => $basePrice,
            'number_of_nights' => $numberOfNights,
            'total_nights_cost' => $totalNightsCost,
        ];
    }

    /**
     * Get array of dates between check-in and check-out
     *
     * @param string $checkIn
     * @param string $checkOut
     * @return array
     */
    protected function getDateRange($checkIn, $checkOut)
    {
        $dates = [];
        $current = \Carbon\Carbon::parse($checkIn);
        $end = \Carbon\Carbon::parse($checkOut);

        while ($current->lt($end)) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $dates;
    }

    /**
     * Get property photos
     */
    public function getPhotosAttribute()
    {
        $mediaItems = $this->getMedia('default');
        $photos = [];

        foreach ($mediaItems as $mediaItem) {
            array_push($photos, $mediaItem->getFullUrl());
        }
        return $photos;
    }

    /**
     * Get main property photo
     */
    public function getMainPhotoAttribute()
    {
        return $this->getFirstMediaUrl('default');
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->useFallbackUrl(url('images/default-property.png'));
    }








    //MISC
    public function getShareableLinkAttribute()
    {
        return route('dynamic.property', $this->id);
    }

    public function getDeepLinkAttribute()
    {
        $scheme = env('dynamic_link.scheme', 'glover');
        return "{$scheme}://property/{$this->id}";
    }
}
