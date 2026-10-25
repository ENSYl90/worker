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
        Schema::create('property_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained('properties')->onDelete('cascade');

            $table->date('date');
            $table->boolean('is_available')->default(1);
            $table->decimal('price_override', 10, 2)->nullable();
            $table->enum('reason', ['available', 'booked', 'blocked', 'maintenance'])->default('available');
            $table->unsignedBigInteger('booking_order_id')->nullable();
            $table->timestamps();

            // Unique constraint: one entry per property/room/date combination
            $table->unique(['property_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_availabilities');
    }
};
