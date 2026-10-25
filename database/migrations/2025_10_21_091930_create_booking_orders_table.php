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
        Schema::create('booking_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');

            $table->date('check_in_date');
            $table->time('check_in_time')->nullable();
            $table->date('check_out_date');
            $table->time('check_out_time')->nullable();
            $table->integer('number_of_guests')->default(1);
            $table->integer('number_of_adults')->default(1);
            $table->integer('number_of_children')->default(0);
            $table->integer('number_of_nights');
            $table->integer('number_of_infants');
            $table->integer('number_of_pets');
            $table->decimal('base_price', 10, 2);
            $table->decimal('total_nights_cost', 10, 2);
            $table->enum('booking_type', ['room', 'entire_property'])->default('entire_property');
            $table->text('special_requests')->nullable();
            $table->foreignId('cancellation_policy_id')->nullable()->constrained('cancellation_policies')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_orders');
    }
};
