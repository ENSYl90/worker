<?php

namespace App\Upgrades;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\VendorType;

class Upgrade86 extends BaseUpgrade
{

    public $versionName = "1.8.02";
    //Runs or migrations to be done on this version
    public function run()
    {
        //create a booking module
        $bookingVendorType = VendorType::where("slug", "booking")->first();
        if ($bookingVendorType == null) {
            $vendorType = new VendorType();
            $vendorType->name = "Booking";
            $vendorType->color = "#def7e5";
            $vendorType->description = "Book hotels, apartments, and short-term rentals easily with trusted local hosts.";
            $vendorType->is_active = true;
            $vendorType->slug = "booking";
            $vendorType->save();
        }

        // Booking System - Step 1: Core Tables
        if (!Schema::hasTable('cancellation_policies')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_20_222258_create_cancellation_policies_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('property_types')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_12_25_144501_create_property_types_table.php",
                '--force' => true,
            ]);
        }
        if (!Schema::hasTable('amenities')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_20_222300_create_amenities_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('properties')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_20_222259_create_properties_table.php",
                '--force' => true,
            ]);
        }

        if (Schema::hasTable('properties') && !Schema::hasColumn('properties', 'property_type_id')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_12_25_145306_add_property_type_id_to_properties_table.php",
                '--force' => true,
            ]);
        }


        if (!Schema::hasTable('amenity_category')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_22_065809_create_amenity_category_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('amenity_property')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_20_222300_create_amenity_property_table.php",
                '--force' => true,
            ]);
        }


        // Booking System - Step 2: Booking & Fee Tables
        if (!Schema::hasTable('booking_fee_types')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_21_091931_create_booking_fee_types_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('booking_orders')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_21_091930_create_booking_orders_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('property_availabilities')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_21_091929_create_property_availabilities_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('booking_guests')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_21_091932_create_booking_guests_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('property_fees')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_21_091933_create_property_fees_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('booking_order_fees')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_21_091934_create_booking_order_fees_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('property_reviews')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_12_27_110000_create_property_reviews_table.php",
                '--force' => true,
            ]);
        }

        // Booking System - Step 3: Reviews Enhancement
        if (!Schema::hasColumn('reviews', 'cleanliness_rating')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_21_092442_add_booking_rating_columns_to_reviews_table.php",
                '--force' => true,
            ]);
        }

        // Booking System - Step 4: Add is_host_residing column to properties table
        if (!Schema::hasColumn('properties', 'is_host_residing')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_12_28_095442_add_is_host_residing_to_properties_table.php",
                '--force' => true,
            ]);
        }

        // property_id to favourites table
        if (!Schema::hasColumn('favourites', 'property_id')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_12_28_091737_add_property_id_to_favourites_table.php",
                '--force' => true,
            ]);
        }

        // Create permissions for booking system management
        Permission::firstorcreate(['name' => "manage-properties"]);
        Permission::firstorcreate(['name' => "manage-bookings"]);
        Permission::firstorcreate(['name' => "view-all-bookings"]);
        Permission::firstorcreate(['name' => "view-my-bookings"]);
        Permission::firstorcreate(['name' => "manage-amenities"]);
        Permission::firstorcreate(['name' => "manage-property-types"]);
        Permission::firstorcreate(['name' => "manage-cancellation-policies"]);
        Permission::firstorcreate(['name' => "manage-booking-fee-types"]);
        Permission::firstorcreate(['name' => "manage-property-types"]);

        // Give admin role access to booking management
        $adminRole = Role::firstorcreate([
            'name' => 'admin'
        ], [
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo("manage-properties");
        $adminRole->givePermissionTo("manage-bookings");
        $adminRole->givePermissionTo("view-all-bookings");
        $adminRole->givePermissionTo("manage-amenities");
        $adminRole->givePermissionTo("manage-cancellation-policies");
        $adminRole->givePermissionTo("manage-property-types");
        $adminRole->givePermissionTo("manage-booking-fee-types");
        $adminRole->givePermissionTo("manage-property-types");

        // Give manager role access to property and booking management
        $managerRole = Role::firstorcreate([
            'name' => 'manager'
        ], [
            'guard_name' => 'web'
        ]);
        $managerRole->givePermissionTo("manage-properties");
        $managerRole->givePermissionTo("manage-bookings");
        $managerRole->givePermissionTo("view-my-bookings");
    }
}
