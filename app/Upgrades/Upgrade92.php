<?php

namespace App\Upgrades;

use App\Services\DriverSubscriptionService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Upgrade92 extends BaseUpgrade
{

    public $versionName = "1.8.40";
    //Runs or migrations to be done on this version
    public function run()
    {

        //add permission:manage-driver-subscriptions
        //assign permission to admin role
        $permissionList = ["manage-api-security", "manage-driver-subscriptions"];
        $adminRole = Role::where('name', 'admin')->first();
        foreach ($permissionList as $value) {
            $permission = Permission::firstOrCreate(['name' => $value]);
            $adminRole->givePermissionTo($permission);
        }



        //add missing table migration: create_driver_subscriptions_table.php
        if (!Schema::hasTable('driver_subscriptions')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2026_06_04_000001_create_driver_subscriptions_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('driver_subscription_histories')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2026_06_04_000003_create_driver_subscription_histories_table.php",
                '--force' => true,
            ]);
        }

        if (Schema::hasTable('driver_subscription_histories') && !Schema::hasColumn('driver_subscription_histories', 'amount')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2026_06_06_000001_add_amount_to_driver_subscription_histories_table.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasTable('api_keys')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2026_06_06_000001_create_api_keys_table.php",
                '--force' => true,
            ]);
        }

        //setup driver subscription settings if not exists
        (new DriverSubscriptionService())->setupSetting();

        //import/export 
        if (!Schema::hasTable('import_export_jobs')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2026_06_08_000001_create_import_export_jobs_table.php",
                '--force' => true,
            ]);
        }


    }
}
