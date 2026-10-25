<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


use App\Traits\DriverLocationRedisTrait;

class DriverLocation extends Model
{
    use HasFactory, SoftDeletes, DriverLocationRedisTrait;

    protected $casts = [
        'driver_id' => 'integer',
        'lat' => 'double',
        'lng' => 'double',
        'rotation' => 'double',
    ];
    protected $fillable = [
        'driver_id',
        'lat',
        'lng',
        'rotation'
    ];


    public function driver()
    {
        return $this->belongsTo('App\Models\User', 'driver_id', 'id')->withTrashed();
    }


    // Helpful method to check if location is valid
    public function isValidLocation(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null
            && $this->latitude >= -90
            && $this->latitude <= 90
            && $this->longitude >= -180
            && $this->longitude <= 180;
    }

    // Accessor for formatted coordinates
    public function getFormattedCoordinatesAttribute()
    {
        return Str::of($this->latitude)
            ->append(', ')
            ->append($this->longitude);
    }

    // Scope to find locations within a certain radius
    public function scopeWithinRadius($query, $latitude, $longitude, $radiusInKm = 10)
    {
        // Haversine formula for calculating distance
        return $query->select('*')
            ->selectRaw('
                6371 * 2 * ASIN(
                    SQRT(
                        POWER(SIN((' . $latitude . ' - latitude) * PI()/180 / 2), 2) +
                        COS(' . $latitude . ' * PI()/180) * COS(latitude * PI()/180) *
                        POWER(SIN((' . $longitude . ' - longitude) * PI()/180 / 2), 2)
                    )
                ) as distance
            ')
            ->havingRaw('distance <= ?', [$radiusInKm])
            ->orderBy('distance');
    }

    /**
     * Smart method to get a Driver's Location
     * Retrieves from Redis if queued, otherwise falls back to Database.
     * 
     * @param int $driverId
     * @return \App\Models\DriverLocation|null
     */
    public static function getForDriver($driverId)
    {
        // 1. Check if we should use Redis
        if (isQueueRedis()) {
            $instance = new static;
            $redisData = $instance->getDriverLocationFromRedis($driverId);
            
            if ($redisData) {
                // Return a mocked eloquent model instance using the Redis data
                // This ensures calling code that expects an Eloquent model doesn't break
                return new self([
                    'driver_id' => $redisData['driver_id'] ?? $driverId,
                    'lat' => $redisData['lat'],
                    'lng' => $redisData['lng'],
                    'rotation' => $redisData['rotation'] ?? 0,
                ]);
            }
        }

        // 2. Fallback to Database
        return self::where('driver_id', $driverId)->first();
    }
}