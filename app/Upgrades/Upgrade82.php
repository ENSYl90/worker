<?php

namespace App\Upgrades;


use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Upgrade82 extends BaseUpgrade
{

    public $versionName = "1.7.71.1";
    //Runs or migrations to be done on this version
    public function run()
    {
        //add new columns to order_stops table
        //check if has address column
        if (!Schema::hasColumn('order_stops', 'address')) {
            Schema::table('order_stops', function ($table) {
                $table->string('address')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
            });
        }
    }
}