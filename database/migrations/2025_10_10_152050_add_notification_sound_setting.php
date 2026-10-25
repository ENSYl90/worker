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
        Schema::create('notification_sound_settings', function (Blueprint $table) {
            $table->id();
            $table->string('sound_file')->default('notification-1.mp3');
            $table->boolean('sound_enabled')->default(true);
            $table->integer('sound_volume')->default(30); // 0-100
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_sound_settings');
    }
};
