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
        if (!Schema::hasColumn('properties', 'featured')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->boolean('featured')->default(false)->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('properties', 'featured')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('featured');
            });
        }
    }
};
