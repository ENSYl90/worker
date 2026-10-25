<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn("booking_orders", "number_of_infants")) {
            Schema::table('booking_orders', function (Blueprint $table) {
                $table->integer('number_of_infants')->default(0)->after('number_of_children');
                $table->integer('number_of_pets')->default(0)->after('number_of_infants');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn("booking_orders", "number_of_infants")) {
            Schema::table('booking_orders', function (Blueprint $table) {
                $table->dropColumn(['number_of_infants', 'number_of_pets']);
            });
        }
    }
};
