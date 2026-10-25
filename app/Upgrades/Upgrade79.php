<?php

namespace App\Upgrades;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class Upgrade79 extends BaseUpgrade
{

    public $versionName = "1.7.70.2";
    //Runs or migrations to be done on this version
    public function run()
    {
        $tableExists = Schema::hasTable('currency_exchange_rates');
        if (!$tableExists) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2025_05_23_111806_create_currency_exchange_rates_table.php",
                '--force' => true,
            ]);
        }
        $tableExists = Schema::hasTable('telescope_entries');
        if (!$tableExists) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/2018_08_08_100000_create_telescope_entries_table.php",
                '--force' => true,
            ]);
        }


        //TELESCOPE_ENABLED = true

    }
}
