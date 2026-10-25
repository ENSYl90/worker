<?php

namespace App\Services;

use Beste\Geohash;
use Illuminate\Broadcasting\Channel;

class DriverLocationGeohashService
{
    public function precisionFromRadiusKm(float $radiusInKm): int
    {
        if ($radiusInKm <= 0.0012) {
            return 10;
        } elseif ($radiusInKm <= 0.0047) {
            return 9;
        } elseif ($radiusInKm <= 0.038) {
            return 8;
        } elseif ($radiusInKm <= 0.152) {
            return 7;
        } elseif ($radiusInKm <= 1.2) {
            return 6;
        } elseif ($radiusInKm <= 4.9) {
            return 5;
        } elseif ($radiusInKm <= 39) {
            return 4;
        } elseif ($radiusInKm <= 156) {
            return 3;
        } elseif ($radiusInKm <= 1250) {
            return 2;
        } elseif ($radiusInKm <= 5000) {
            return 1;
        }

        return 9;
    }

    public function encode(float $lat, float $lng, ?int $precision = null): string
    {
        return Geohash::encode($lat, $lng, $precision);
    }

    public function encodeForRadius(float $lat, float $lng, float $radiusInKm): string
    {
        return $this->encode($lat, $lng, $this->precisionFromRadiusKm($radiusInKm));
    }

    public function driverLocationChannelNameFromGeohash(string $geohash): string
    {
        return "drivers.locations.{$geohash}.updated";
    }

    public function driverLocationChannelName(float $lat, float $lng, ?float $radiusInKm = null): string
    {
        $geohash = $this->encodeForRadius(
            $lat,
            $lng,
            $radiusInKm ?? $this->defaultDriverSearchRadius()
        );

        return $this->driverLocationChannelNameFromGeohash($geohash);
    }

    public function publicDriverLocationChannel(float $lat, float $lng, ?float $radiusInKm = null): Channel
    {
        return new Channel($this->driverLocationChannelName($lat, $lng, $radiusInKm));
    }

    public function defaultDriverSearchRadius(): float
    {
        return (float) setting('driverSearchRadius', 10);
    }
}
