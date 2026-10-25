<?php

namespace App\Upgrades;


use Illuminate\Support\Facades\Schema;

class Upgrade78 extends BaseUpgrade
{

    public $versionName = "1.7.70.1";
    //Runs or migrations to be done on this version
    public function run()
    {
        if (!Schema::hasColumn('services', 'deleted_at')) {
            Schema::table('services', function ($table) {
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('products', 'deleted_at')) {
            Schema::table('products', function ($table) {
                $table->softDeletes();
            });
        }
        if (!Schema::hasColumn('taxi_orders', 'deleted_at')) {
            Schema::table('taxi_orders', function ($table) {
                $table->softDeletes();
            });
        }
        if (!Schema::hasColumn('favourites', 'service_id')) {
            Schema::table('favourites', function ($table) {
                $table->foreignId('service_id')->nullable()->constrained()->onDelete('cascade')->after("product_id");
            });
        }
    }
}