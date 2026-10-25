<?php

namespace App\Console\Commands;

use Database\Seeders\PropertyReportReasonSeeder;
use Database\Seeders\PropertyReviewSeeder;
use Illuminate\Console\Command;
use Database\Seeders\BookingCategorySeeder;
use Database\Seeders\AmenitySeeder;

use Database\Seeders\CancellationPolicySeeder;
use Database\Seeders\PropertySeeder;
use Database\Seeders\BookingFeeTypeSeeder;
use Database\Seeders\BookingOrderSeeder;
use Database\Seeders\PropertyTypeSeeder;

class SeedBookingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:booking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed all booking related data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Seeding Booking Data...');

        if ($this->confirm('This will wipe all booking related data. Do you want to continue?', true)) {
            $this->info('Clearing old data...');
            \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

            \Illuminate\Support\Facades\DB::table('booking_order_fees')->truncate();
            \Illuminate\Support\Facades\DB::table('booking_guests')->truncate();
            \Illuminate\Support\Facades\DB::table('booking_orders')->truncate();
            \Illuminate\Support\Facades\DB::table('property_reviews')->truncate();
            \Illuminate\Support\Facades\DB::table('property_availabilities')->truncate();
            \Illuminate\Support\Facades\DB::table('property_fees')->truncate();
            \Illuminate\Support\Facades\DB::table('amenity_property')->truncate();
            \Illuminate\Support\Facades\DB::table('properties')->truncate();
            \Illuminate\Support\Facades\DB::table('cancellation_policies')->truncate();
            \Illuminate\Support\Facades\DB::table('property_types')->truncate();
            \Illuminate\Support\Facades\DB::table('booking_fee_types')->truncate();
            \Illuminate\Support\Facades\DB::table('amenities')->truncate();

            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

            //Now: CLEAR ALL mEDIA Models related to any of the booking Models
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
            \Illuminate\Support\Facades\DB::table('media')->whereIn('model_type', [
                'App\Models\BookingOrder',
                'App\Models\BookingGuest',
                'App\Models\BookingFeeType',
                'App\Models\BookingOrderFee',
                'App\Models\Property',
                'App\Models\PropertyAvailability',
                'App\Models\PropertyFee',
                'App\Models\PropertyReview',
                'App\Models\PropertyType',
                'App\Models\CancellationPolicy',
                'App\Models\Amenity',
                'App\Models\AmenityProperty',
            ])->delete();
            $this->info('Old data cleared!');
        }

        $seeders = [
                // BookingCategorySeeder::class,
            AmenitySeeder::class,
            CancellationPolicySeeder::class,
            PropertyTypeSeeder::class,
            PropertySeeder::class,
            BookingFeeTypeSeeder::class,
            BookingOrderSeeder::class,
            PropertyReviewSeeder::class,
            PropertyReportReasonSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            $this->info("Running {$seeder}...");
            $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        $this->info('Booking Data Seeded Successfully!');
        return 0;
    }
}
