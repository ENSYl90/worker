<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\DriverLocation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetInactiveDriversOffline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!isUsingWebsocket()) {
            return;
        }
        // Get the inactivity timeout in minutes from settings (default: 30 minutes)
        $inactivityTimeout = (int) setting('driverInactivityTimeout', 30);
        $cutoffTime = Carbon::now()->subMinutes($inactivityTimeout);

        // Find all online drivers whose location hasn't been updated recently and the driver is_online is true
        $inactiveDriverIds = DriverLocation::whereTime('updated_at', '<', $cutoffTime)
            ->whereHas('driver', function ($query) {
                $query->where('is_online', true);
            })
            ->pluck('driver_id')
            ->unique();

        if ($inactiveDriverIds->isNotEmpty()) {
            // Set these drivers to offline
            $drivers = User::whereIn('id', $inactiveDriverIds)
                ->where('is_online', true)
                ->get();

            foreach ($drivers as $driver) {
                User::where('id', $driver->id)->update(['is_online' => false]);
                $driver->is_online = false;
                event(new \App\Events\DriverSetOfflineEvent($driver));
            }
        }
    }
}
