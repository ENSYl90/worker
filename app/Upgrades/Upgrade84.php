<?php

namespace App\Upgrades;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Upgrade84 extends BaseUpgrade
{

    public $versionName = "1.8.00";
    //Runs or migrations to be done on this version
    public function run()
    {

        $tableExists = Schema::connection(config('activitylog.database_connection'))
            ->hasTable(config('activitylog.table_name'));
        if (!$tableExists) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_02_101046_create_activity_log_table.php",
                '--force' => true,
            ]);
        }
        if (!Schema::hasTable('notification_settings')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_10_121213_create_notification_settings_table.php",
                '--force' => true,
            ]);
        }
        if (!Schema::hasTable('notification_sound_settings')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_10_152050_add_notification_sound_setting.php",
                '--force' => true,
            ]);
        }

        if (!Schema::hasColumn('currencies', 'is_active')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_05_081003_add_is_active_to_currencies_table.php",
                '--force' => true,
            ]);
        }

        Permission::firstorcreate(['name' => "manage-modules"]);
        Permission::firstorcreate(['name' => "edit-system-translations"]);
        Permission::firstorcreate(['name' => "view-activity-logs"]);
        $adminRole = Role::firstorcreate([
            'name' => 'admin'
        ], [
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo("manage-modules");
        $adminRole->givePermissionTo("edit-system-translations");
        $adminRole->givePermissionTo("view-activity-logs");
    }
}
