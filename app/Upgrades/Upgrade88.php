<?php

namespace App\Upgrades;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Upgrade88 extends BaseUpgrade
{

    public $versionName = "1.8.10";
    //Runs or migrations to be done on this version
    public function run()
    {

        //remove permission: view-vendors from manager role
        $managerRole = Role::findByName('manager');
        $managerRole->revokePermissionTo('view-vendors');

        if (!Schema::hasColumn('users', 'uuid')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2026_01_03_161627_add_uuid_to_users_table.php",
                '--force' => true,
            ]);
        }


    }
}
