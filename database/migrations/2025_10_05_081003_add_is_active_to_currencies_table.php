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
        //check if column exists
        if (!Schema::hasColumn('currencies', 'is_active')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->boolean('is_active')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //check if column exists
        if (Schema::hasColumn('currencies', 'is_active')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
