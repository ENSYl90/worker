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
        Schema::create('currency_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('rate', 15, 8); // Rate from base currency to this currency
            $table->date('effective_date');
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for better performance
            $table->index(['currency_id', 'effective_date']);
            $table->index('effective_date');

            // Add a unique constraint that works with soft deletes
            // This will be handled at the application level instead
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_exchange_rates');
    }
};
