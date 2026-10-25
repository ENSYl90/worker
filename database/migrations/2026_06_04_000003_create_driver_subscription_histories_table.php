<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverSubscriptionHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('driver_subscription_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_subscription_id')->constrained('driver_subscriptions');
            $table->foreignId('driver_id')->constrained('users');
            $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions');
            $table->string('code')->nullable()->unique();
            $table->enum('type', ['orders', 'time'])->default('time');
            $table->enum('status', ['pending', 'failed', 'cancelled', 'successful', 'expired'])->default('pending');
            $table->double('amount', 15, 8)->default(0.00);
            $table->unsignedInteger('order_limit')->nullable();
            $table->unsignedInteger('remaining_orders')->nullable();
            $table->unsignedInteger('completed_orders')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_subscription_histories');
    }
}
