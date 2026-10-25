<?php

namespace App\Console\Commands;

use App\Traits\FirebaseMessagingTrait;

class DemoDataRestore extends SyncDemoData
{

    use FirebaseMessagingTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:demo-data-restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demo Data Restore At Midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('app.env') == 'production') {
            $this->error("Seeding failed because server is in production mode");
            return;
        }
        if (!((bool) env("ALLOW_DEMO_RESET", false))) {
            $this->error("Seeding failed because server not allowed");
            return;
        }

        //notification
        $this->sendPlainFirebaseNotification(
            "all",
            __("Server Data is about to be reset"),
            __("Please note that all user created content like products, services, vendors will be cleared and demo data will be restored. Thank you"),
        );

        //
        parent::handle();
    }
}
