<?php

namespace App\Events;

use App\Services\DriverLocationGeohashService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationGeohashUpdatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driverId;
    public $locationData;
    public $radiusInKm;
    public $vehicleTypeId;

    public function __construct($driverId, array $locationData, ?float $radiusInKm = null, $vehicleTypeId = null)
    {
        $this->driverId = $driverId;
        $this->locationData = $locationData;
        $this->radiusInKm = $radiusInKm;
        $this->vehicleTypeId = $vehicleTypeId;
    }

    public function broadcastOn(): array
    {
        $lat = $this->latitude();
        $lng = $this->longitude();

        if ($lat === null || $lng === null) {
            return [];
        }

        return [
            $this->geohashService()->publicDriverLocationChannel($lat, $lng, $this->radiusInKm),
        ];
    }

    public function broadcastAs()
    {
        return 'DriverLocationGeohashUpdated';
    }

    public function broadcastWith(): array
    {
        $lat = $this->latitude();
        $lng = $this->longitude();

        if ($lat === null || $lng === null) {
            return $this->locationData;
        }

        $service = $this->geohashService();
        $geohash = $service->encodeForRadius(
            $lat,
            $lng,
            $this->radiusInKm ?? $service->defaultDriverSearchRadius(),
        );

        $locationData = $this->locationData;
        unset($locationData['driver_id']);

        return array_merge($locationData, [
            'vehicle_type_id' => $this->vehicleTypeId,
            'geohash' => $geohash,
            'geohash_channel' => $service->driverLocationChannelNameFromGeohash($geohash),
        ]);
    }

    private function geohashService(): DriverLocationGeohashService
    {
        return app(DriverLocationGeohashService::class);
    }

    private function latitude(): ?float
    {
        return isset($this->locationData['lat']) && is_numeric($this->locationData['lat'])
            ? (float) $this->locationData['lat']
            : null;
    }

    private function longitude(): ?float
    {
        return isset($this->locationData['lng']) && is_numeric($this->locationData['lng'])
            ? (float) $this->locationData['lng']
            : null;
    }
}
