<?php
namespace App\Traits;
use Illuminate\Support\Facades\Redis;
trait DriverLocationRedisTrait
{
    /**
     * Save driver location and details to Redis.
     *
     * @param int $driverId
     * @param float $lat
     * @param float $lng
     * @param float|null $rotation
     * @param int|null $vehicleTypeId
     * @return void
     */
    public function saveDriverLocationToRedis($driverId, $lat, $lng, $rotation = 0, $vehicleTypeId = null)
    {
        Redis::command('GEOADD', ['driver_locations', $lng, $lat, (string) $driverId]);
        $driverData = [
            'id' => $driverId,
            'driver_id' => $driverId,
            'lat' => $lat,
            'lng' => $lng,
            'rotation' => $rotation,
            'vehicle_type_id' => $vehicleTypeId,
            'updated_at' => now()->toDateTimeString(),
        ];

        Redis::command('HSET', ['driver_details', (string) $driverId, json_encode($driverData)]);

    }

    /**
     * Retrieve driver data based on proximity, and optionally filter by vehicle_type_id.
     *
     * @param float $lat
     * @param float $lng
     * @param float $radius In kilometers
     * @param int|null $vehicleTypeId
     * @param int|null $limit
     * @return array
     */
    public function getDriversFromRedis($lat, $lng, $radius = 10, $vehicleTypeId = null, $limit = 10)
    {
        // GEOSEARCH replaces deprecated GEORADIUS (requires Redis 6.2+)
        $driversLocationKey = config('database.redis.options.prefix') . 'driver_locations';
        $results = Redis::executeRaw([
            'GEOSEARCH',
            $driversLocationKey,
            'FROMLONLAT', $lng, $lat,
            'BYRADIUS', $radius, 'km',
            'ASC',
            'WITHDIST',
        ]);

        $drivers = [];

        if (empty($results)) {
            return $drivers;
        }

        foreach ($results as $result) {
            // GEOSEARCH with WITHDIST returns [member, distance] per match
            if (is_array($result) && count($result) >= 2) {
                $driverId = $result[0];
                $distance = (float) $result[1];
            } else {
                continue;
            }

            // Fetch driver details from Hash
            $driverDataRaw = Redis::command('HGET', ['driver_details', (string) $driverId]);

            if ($driverDataRaw) {
                $driverData = json_decode($driverDataRaw, true);

                // Filter by vehicle_type_id if provided
                if ($vehicleTypeId !== null) {
                    if (!isset($driverData['vehicle_type_id']) || $driverData['vehicle_type_id'] != $vehicleTypeId) {
                        continue;
                    }
                }

                // Add calculated distance to the data structure
                $driverData['distance'] = $distance;
                $drivers[] = $driverData;
            }
        }

        if ($limit && count($drivers) > $limit) {
            return array_slice($drivers, 0, $limit);
        }

        return $drivers;
    }

    /**
     * Retrieve a specific driver's location and details from Redis.
     *
     * @param int $driverId
     * @return array|null
     */
    public function getDriverLocationFromRedis($driverId)
    {
        $driverDataRaw = Redis::command('HGET', ['driver_details', (string) $driverId]);

        if ($driverDataRaw) {
            return json_decode($driverDataRaw, true);
        }

        return null;
    }

    /**
     * Clear all driver location records from Redis.
     * This is used when the queue is enabled and we need to clean up.
     *
     * @return void
     */
    public function clearDriversRedisRecord()
    {
        // Remove all members from the sorted set 'driver_locations'
        Redis::command('DEL', ['driver_locations']);

        // Remove all fields from the hash 'driver_details'
        Redis::command('DEL', ['driver_details']);
    }
}