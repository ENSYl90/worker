<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'title')) {
                $table->string('title')->nullable()->after('link');
            }
            if (!Schema::hasColumn('banners', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('banners', 'cta_button_title')) {
                $table->string('cta_button_title')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('banners', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('banners', 'cta_button_title')) {
                $table->dropColumn('cta_button_title');
            }
        });
    }
};
