<?php

namespace App\Upgrades;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Upgrade89 extends BaseUpgrade
{

    public $versionName = "1.8.10+1";
    //Runs or migrations to be done on this version
    public function run()
    {

        //add permission: view-property-report
        //assign permission to admin and manager roles
        $permission = Permission::where('name', 'view-property-report')->first();
        if (!$permission) {
            $permission = Permission::create(['name' => 'view-property-report']);
        }
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $managerRole->givePermissionTo($permission);
        }

        //add missing column to properties table
        if (!Schema::hasColumn('properties', 'featured')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2026_01_26_084933_add_featured_to_properties_table.php",
                '--force' => true,
            ]);
        }


    }
}
