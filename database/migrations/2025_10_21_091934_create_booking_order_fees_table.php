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
        Schema::create('booking_order_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_order_id')->constrained('booking_orders')->onDelete('cascade');
            $table->foreignId('booking_fee_type_id')->constrained('booking_fee_types')->onDelete('cascade');
            $table->string('name');
            $table->enum('calculation_type', ['fixed', 'percentage', 'per_night', 'per_guest']);
            $table->decimal('base_amount', 10, 2);
            $table->decimal('calculated_amount', 10, 2);
            $table->boolean('is_taxable')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_order_fees');
    }
};
