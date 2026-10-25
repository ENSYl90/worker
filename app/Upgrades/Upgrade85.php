<?php

namespace App\Upgrades;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Upgrade85 extends BaseUpgrade
{

    public $versionName = "1.8.01";
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

        if (!Schema::hasColumn('currencies', 'is_active')) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_10_05_081003_add_is_active_to_currencies_table.php",
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

        Permission::firstorcreate(['name' => "manage-modules"]);
        Permission::firstorcreate(['name' => "edit-system-translations"]);
        Permission::firstorcreate(['name' => "view-activity-logs"]);
        Permission::firstorcreate(['name' => "manage-notification-settings"]);
        Permission::firstorcreate(['name' => "recieve-system-notifications"]);
        $adminRole = Role::firstorcreate([
            'name' => 'admin'
        ], [
            'guard_name' => 'web'
        ]);
        $adminRole->givePermissionTo("manage-modules");
        $adminRole->givePermissionTo("edit-system-translations");
        $adminRole->givePermissionTo("view-activity-logs");
        $adminRole->givePermissionTo("manage-notification-settings");
        $adminRole->givePermissionTo("recieve-system-notifications");



        //set the MIX_values
        setEnv("MIX_REVERB_APP_KEY", "\"\${REVERB_APP_KEY}\"");
        setEnv("MIX_REVERB_HOST", "\"\${REVERB_HOST}\"");
        setEnv("MIX_REVERB_PORT", "\"\${REVERB_PORT}\"");
        setEnv("MIX_REVERB_SCHEME", "\"\${REVERB_SCHEME}\"");

    }
}
