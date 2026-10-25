<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('driver_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['orders', 'time'])->default('time');
            $table->unsignedInteger('days')->nullable();
            $table->unsignedInteger('order_limit')->nullable();
            $table->decimal('amount', 20, 4)->default(1.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_subscriptions');
    }
}
