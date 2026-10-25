<?php

namespace App\Upgrades;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Upgrade87 extends BaseUpgrade
{

    public $versionName = "1.8.03";
    //Runs or migrations to be done on this version
    public function run()
    {

        //add group column to nav_menus
        if (!Schema::hasColumn('nav_menus', 'group')) {
            Schema::table('nav_menus', function ($table) {
                $table->string('group')->nullable()->after("id");
            });
        }


        //

        if (!Schema::hasTable("property_report_reasons")) {
            Artisan::call("migrate", [
                "--path" => "database/migrations/2025_12_28_120534_create_property_report_reasons_table.php",
                "--force" => true
            ]);
        }


        if (!Schema::hasTable("property_reports")) {
            Artisan::call("migrate", [
                "--path" => "database/migrations/2025_12_28_120534_create_property_reports_table.php",
                "--force" => true
            ]);
        }

        //2025_12_28_200815_add_infants_and_pets_to_booking_orders_table.php
        if (!Schema::hasColumn("booking_orders", "number_of_infants")) {
            Artisan::call("migrate", [
                "--path" => "database/migrations/2025_12_28_200815_add_infants_and_pets_to_booking_orders_table.php",
                "--force" => true
            ]);
        }
        //
        $adminNewPermissions = [
            "manage-property-report-reasons",
            "manage-property-reports"
        ];
        $adminRole = Role::where('name', 'admin')->first();
        foreach ($adminNewPermissions as $permission) {
            Permission::firstorcreate(['name' => $permission]);
            $adminRole->givePermissionTo($permission);
        }


    }
}
