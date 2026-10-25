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
        if (!Schema::hasColumn('properties', 'is_host_residing')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->boolean('is_host_residing')->default(0)->after('is_instant_booking');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('properties', 'is_host_residing')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('is_host_residing');
            });
        }
    }
};
