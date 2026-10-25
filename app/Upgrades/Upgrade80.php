<?php

namespace App\Upgrades;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Upgrade80 extends BaseUpgrade
{

    public $versionName = "1.7.70.3";
    //Runs or migrations to be done on this version
    public function run()
    {
        $tableExists = Schema::hasTable('telescope_entries');
        if (!$tableExists) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2018_08_08_100000_create_telescope_entries_table.php",
                '--force' => true,
            ]);
        }

        //add "edit-order", permission
        Permission::firstorcreate(['name' => "edit-order"]);
        Permission::firstorcreate(['name' => "review-order-payment"]);
        Permission::firstorcreate(['name' => "allow-select-payment-gateway"]);
        $adminRole = Role::firstorcreate([
            'name' => 'admin'
        ], [
            'guard_name' => 'web'
        ]);
        $managerRole = Role::firstorcreate([
            'name' => 'manager'
        ], [
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo("edit-order");
        $adminRole->givePermissionTo("review-order-payment");
        $managerRole->givePermissionTo("edit-order");
        $managerRole->givePermissionTo("allow-select-payment-gateway");
    }
}
