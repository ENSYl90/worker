<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $subscriptions = [
            [
                'name' => 'Basic Plan',
                'days' => 30,
                'qty' => 50,
                'amount' => 10.00,
                'is_active' => true,
            ],
            [
                'name' => 'Premium Plan',
                'days' => 90,
                'qty' => 200,
                'amount' => 25.00,
                'is_active' => true,
            ],
            [
                'name' => 'Professional Plan',
                'days' => 365,
                'qty' => 1000,
                'amount' => 90.00,
                'is_active' => true,
            ],
            [
                'name' => 'Free Trial',
                'days' => 14,
                'qty' => 10,
                'amount' => 0.00,
                'is_active' => true,
            ],
        ];

        foreach ($subscriptions as $subscription) {
            Subscription::updateOrCreate(
                ['name' => $subscription['name']],
                $subscription
            );
        }
    }
}
