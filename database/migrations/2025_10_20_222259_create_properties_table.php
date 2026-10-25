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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('discount_price', 15, 2)->default(0);
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->time('check_in_time_start')->default('14:00');
            $table->time('check_in_time_end')->default('22:00');
            $table->time('check_out_time')->default('11:00');
            $table->integer('max_guests')->default(1);
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->integer('beds')->default(1);
            $table->integer('square_meters')->nullable();
            $table->boolean('is_instant_booking')->default(0);
            $table->boolean('is_host_residing')->default(0);
            $table->integer('minimum_nights')->default(1);
            $table->integer('maximum_nights')->nullable();
            $table->integer('preparation_time')->default(0);
            $table->foreignId('cancellation_policy_id')->nullable()->constrained('cancellation_policies')->onDelete('set null');
            $table->longText('house_rules')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('featured')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
