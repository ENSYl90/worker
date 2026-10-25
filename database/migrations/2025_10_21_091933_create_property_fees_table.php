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
        Schema::create('property_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained('properties')->onDelete('cascade');

            $table->foreignId('booking_fee_type_id')->constrained('booking_fee_types')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('min_amount', 10, 2)->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            // Unique constraint: one fee type per property/room combination
            $table->unique(['property_id', 'booking_fee_type_id'], 'property_fee_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_fees');
    }
};
