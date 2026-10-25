<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('order:dispatch')->everyMinute()->withoutOverlapping();
        $schedule->command('order:cancel')->everyMinute()->withoutOverlapping();
        $schedule->command('vendor:auto:open-close')->everyMinute()->withoutOverlapping();
        $schedule->command('taxi:cancel')->everyMinute()->withoutOverlapping();
        $schedule->command('order:manage')->everySixHours()->withoutOverlapping();

        // Replaced by DispatchScheduledTaxiOrder — a per-order delayed job dispatched at booking time.
        // $schedule->job(new \App\Jobs\ProcessScheduledTaxiOrders)->everyFiveMinutes()->withoutOverlapping();
        //Webscoket disconnect handling is now event-driven via DriverWebsocketDisconnectedListener, 
        // so the periodic job is no longer needed.
        // but we can keep it as a fallback to catch any edge cases where the event might not fire, such as unexpected server crashes or network issues. It will run every 2hours and set any drivers who haven't updated their location within the inactivity timeout to offline.
        $schedule->job(new \App\Jobs\SetInactiveDriversOffline)->everyTwoHours()->withoutOverlapping();

        // If you want to disable the periodic job and rely solely on the event-driven approach, you can comment out or remove the line above.
        // $schedule->job(new \App\Jobs\SetInactiveDriversOffline)->everyTwoHours()->withoutOverlapping();

        //
        try {
            $assignmentType = setting('autoassignmentsystem', 0);
        } catch (\Throwable $e) {
            $assignmentType = 0;
        }
        if ($assignmentType == 1) {
            $schedule->command('order:assign:firestore-on-device')->everyMinute()->withoutOverlapping();
        } else {
            $schedule->command('order:assign')->everyMinute()->withoutOverlapping();
        }

        $schedule->command('order:auto_assignment_cancel')->everyMinute()->withoutOverlapping();
        $schedule->command('subscription:manage')->hourly();
        $schedule->command('order:driver:clear')->hourly();
        //
        $schedule->command('order:cancel-pending-payments')->everyMinute()->withoutOverlapping();
        $schedule->command('wallet:cancel-pending-transactions')->everyMinute()->withoutOverlapping();

        // clear logs on monday, Wednesdays, Saturday at 6am
        $schedule->command('logs:clear')->weekly()->days([1, 3, 6])->at('6:00');
        $schedule->command('app:clear-all')->weekly()->days([1, 3, 6])->at('6:00');

        // demo and action releated commands
        // $schedule->command('app:demo-data-restore')->evenInMaintenanceMode()->sundays()->at("01:00");
        // $schedule->command('app:sync-demo-data')->sundays()->at('12:00');
        $schedule->command('app:demo-data-restore')->daily();
        //
        setting([
            'cronJobLastRun' => \Carbon\Carbon::now()->translatedFormat('d M Y \\a\\t h:i:s a'),
            'cronJobLastRunRaw' => \Carbon\Carbon::now(),
        ])->save();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
