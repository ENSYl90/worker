<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\DriverLocationRedisTrait;

class SeedDriverLocation extends Command
{
    use DriverLocationRedisTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'driver:seed-location {driver_id?} {--lat=} {--lng=} {--rotation=0} {--vehicle_type=} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed driver locations into Redis (supports single driver or all drivers)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $all = $this->option('all');
        $latArg = $this->option('lat');
        $lngArg = $this->option('lng');
        $rotation = (float) $this->option('rotation');
        $vehicleTypeId = $this->option('vehicle_type');

        if ($all) {
            $drivers = \App\Models\User::role('driver')->get();
            $this->info("Seeding locations for " . $drivers->count() . " drivers...");

            foreach ($drivers as $driver) {
                // If center provided, jitter around it. Otherwise, random global.
                if ($latArg && $lngArg) {
                    $lat = (float) $latArg + (rand(-100, 100) / 1000);
                    $lng = (float) $lngArg + (rand(-100, 100) / 1000);
                } else {
                    $lat = rand(-90000, 90000) / 1000;
                    $lng = rand(-180000, 180000) / 1000;
                }

                $this->saveDriverLocationToRedis(
                    $driver->id,
                    $lat,
                    $lng,
                    $rotation,
                    $vehicleTypeId
                );
            }

            $this->info("Successfully seeded locations for all drivers.");
            return 0;
        }

        // Single driver logic
        $driverId = $this->argument('driver_id');
        if (!$driverId || !$latArg || !$lngArg) {
            $this->error("Missing required arguments for single driver seeding. Use --all to seed everyone.");
            return 1;
        }

        $lat = (float) $latArg;
        $lng = (float) $lngArg;

        try {
            $this->saveDriverLocationToRedis(
                $driverId,
                $lat,
                $lng,
                $rotation,
                $vehicleTypeId
            );

            $this->info("Successfully seeded location for driver ID: {$driverId}");
            $this->line("Lat: {$lat}, Lng: {$lng}");
        } catch (\Exception $e) {
            $this->error("Failed to seed location: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
