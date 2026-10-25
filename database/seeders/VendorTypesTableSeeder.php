<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate or delete the existing vendor types
        DB::table('vendor_types')->delete();

        // Insert fresh records
        DB::table('vendor_types')->insert([
            [
                'id' => 1,
                'name' => '{"en":"SendIt"}',
                'color' => '#def7e5',
                'description' => '{"en":"Quick and secure delivery of your packages from door to door, anytime you need."}',
                'slug' => 'parcel',
                'is_active' => 1,
                'in_order' => 7,
                'created_at' => '2021-06-30 10:45:53',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => '{"en":"Eats"}',
                'color' => '#def7e5',
                'description' => '{"en":"Order your favorite meals from top restaurants and get them delivered fast and fresh."}',
                'slug' => 'food',
                'is_active' => 1,
                'in_order' => 2,
                'created_at' => '2021-06-30 10:45:53',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => '{"en":"Market"}',
                'color' => '#def7e5',
                'description' => '{"en":"Shop groceries, essentials, and household items conveniently from local stores."}',
                'slug' => 'grocery',
                'is_active' => 1,
                'in_order' => 3,
                'created_at' => '2021-06-30 13:59:15',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'name' => '{"en":"MediDrop"}',
                'color' => '#def7e5',
                'description' => '{"en":"Order medicines and health supplies safely and conveniently from trusted pharmacies."}',
                'slug' => 'pharmacy',
                'is_active' => 1,
                'in_order' => 4,
                'created_at' => '2021-06-30 14:01:27',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'name' => '{"en":"Assist"}',
                'color' => '#def7e5',
                'description' => '{"en":"Book trusted professionals for cleaning, repairs, beauty, and other essential services."}',
                'slug' => 'service',
                'is_active' => 1,
                'in_order' => 6,
                'created_at' => '2021-07-15 00:38:10',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'name' => '{"en":"Ride"}',
                'color' => '#def7e5',
                'description' => '{"en":"Book reliable and affordable rides anytime, anywhere for your daily commutes."}',
                'slug' => 'taxi',
                'is_active' => 1,
                'in_order' => 8,
                'created_at' => '2021-07-15 00:38:10',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 7,
                'name' => '{"en":"Booking"}',
                'color' => '#def7e5',
                'description' => '{"en":"Book hotels, apartments, and short-term rentals easily with trusted local hosts."}',
                'slug' => 'booking',
                'is_active' => 1,
                'in_order' => 5,
                'created_at' => '2022-01-14 14:44:52',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 8,
                'name' => '{"en":"Mall"}',
                'color' => '#def7e5',
                'description' => '{"en":"Shop from a wide range of fashion, electronics, and lifestyle products online."}',
                'slug' => 'commerce',
                'is_active' => 1,
                'in_order' => 1,
                'created_at' => '2022-02-09 22:46:30',
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
