<?php

namespace App\Console\Commands;

use App\Events\DriverLocationGeohashUpdatedEvent;
use App\Events\WebsocketDriverLocationUpdatedEvent;
use App\Models\DriverLocation;
use App\Models\User;
use App\Traits\DriverLocationRedisTrait;
use Illuminate\Console\Command;

class FakeDriverMovement extends Command
{
    use DriverLocationRedisTrait;

    protected $signature = 'driver:fake-movement 
                            {count : Number of drivers to simulate} 
                            {lat : Center latitude} 
                            {lng : Center longitude} 
                            {radius : Radius in kilometers} 
                            {--delay=3 : Delay in seconds between updates when looping} 
                            {--loop : Keep moving and updating drivers continuously}';

    protected $description = 'Simulate multiple moving drivers around a center point with random locations inside a radius';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $centerLat = (float) $this->argument('lat');
        $centerLng = (float) $this->argument('lng');
        $radius = (float) $this->argument('radius');
        $delay = (int) $this->option('delay');
        $loop = $this->option('loop');

        if ($count <= 0) {
            $this->error('Number of drivers must be greater than 0.');
            return 1;
        }

        $availableDriversCount = User::role('driver')->count();

        if ($availableDriversCount === 0) {
            $this->error('No existing driver accounts found. Please create driver accounts before running this command.');
            return 1;
        }

        $drivers = User::role('driver')
            ->with('vehicle')
            ->inRandomOrder()
            ->take($count)
            ->get();

        if ($availableDriversCount < $count) {
            $this->warn("Only {$drivers->count()} existing driver account(s) found. Simulating all available drivers.");
        }

        $this->info("Simulating movement for {$drivers->count()} drivers...");

        $driverStates = [];
        foreach ($drivers as $driver) {
            $pos = $this->getRandomLocationInRadius($centerLat, $centerLng, $radius);
            $driverStates[] = [
                'driver' => $driver,
                'lat' => $pos['lat'],
                'lng' => $pos['lng'],
                'rotation' => rand(0, 360),
            ];
        }

        do {
            foreach ($driverStates as &$state) {
                $driver = $state['driver'];

                $dLat = rand(-150, 150) / 1000000;
                $dLng = rand(-150, 150) / 1000000;

                $state['lat'] += $dLat;
                $state['lng'] += $dLng;

                $state['rotation'] = rad2deg(atan2($dLng, $dLat));
                if ($state['rotation'] < 0) {
                    $state['rotation'] += 360;
                }

                $driverLocation = DriverLocation::updateOrCreate(
                    ['driver_id' => $driver->id],
                    [
                        'lat' => $state['lat'],
                        'lng' => $state['lng'],
                        'rotation' => $state['rotation'],
                    ]
                );

                $vehicleTypeId = $driver->vehicle ? $driver->vehicle->vehicle_type_id : null;

                if (isQueueRedis()) {
                    $this->saveDriverLocationToRedis(
                        $driver->id,
                        $state['lat'],
                        $state['lng'],
                        $state['rotation'],
                        $vehicleTypeId
                    );

                    $driverLocation = new DriverLocation([
                        'driver_id' => $driver->id,
                        'lat' => $state['lat'],
                        'lng' => $state['lng'],
                        'rotation' => $state['rotation'],
                    ]);
                }

                event(new WebsocketDriverLocationUpdatedEvent($driver->id, $driverLocation->toArray()));
                event(new DriverLocationGeohashUpdatedEvent($driver->id, $driverLocation->toArray(), $radius, $vehicleTypeId));

                $this->line("Driver #{$driver->id} ({$driver->name}): Lat: {$state['lat']}, Lng: {$state['lng']}, Rotation: " . round($state['rotation']) . "°");
            }

            if ($loop) {
                $this->info("Sleeping for {$delay} seconds... Press Ctrl+C to stop.");
                sleep($delay);
            }
        } while ($loop);

        $this->info("Simulation completed.");
        return 0;
    }

    private function getRandomLocationInRadius($centerLat, $centerLng, $radiusKm)
    {
        $r = $radiusKm * sqrt(mt_rand() / mt_getrandmax());
        $theta = (mt_rand() / mt_getrandmax()) * 2 * M_PI;

        $deltaLat = $r * cos($theta) / 111.045;
        $deltaLng = $r * sin($theta) / (111.045 * cos(deg2rad($centerLat)));

        return [
            'lat' => $centerLat + $deltaLat,
            'lng' => $centerLng + $deltaLng,
        ];
    }
}
