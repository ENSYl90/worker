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
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // Order, User, Vendor, Payment, SubscriptionVendor, Status
            $table->string('action'); // created, updated, deleted
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            // Ensure unique combination of model and action
            $table->unique(['model_type', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
