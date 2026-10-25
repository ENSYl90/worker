<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmountToDriverSubscriptionHistoriesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('driver_subscription_histories') && !Schema::hasColumn('driver_subscription_histories', 'amount')) {
            Schema::table('driver_subscription_histories', function (Blueprint $table) {
                $table->double('amount', 15, 8)->default(0.00)->after('status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('driver_subscription_histories') && Schema::hasColumn('driver_subscription_histories', 'amount')) {
            Schema::table('driver_subscription_histories', function (Blueprint $table) {
                $table->dropColumn('amount');
            });
        }
    }
}
