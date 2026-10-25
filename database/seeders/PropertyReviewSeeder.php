<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyReview;
use Faker\Factory as Faker;

class PropertyReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Get users with 'client' role
        // Assuming Spatie Permission is used, or a similar setup where we can filter by role
        // If query fails, we might need to adjust. For now assuming `role` scope exists or we iterate.
        // To be safe and robust, let's try to get users who are likely clients.
        // If User model has `role` method (spatie), we can use it.

        $clients = User::role('client')->get();
        if ($clients->isEmpty()) {
            // If no clients found via scope, maybe just get some random users
            $clients = User::take(10)->get();
        }

        $properties = Property::all();

        if ($properties->isEmpty()) {
            $this->command->info('No properties found to review.');
            return;
        }

        if ($clients->isEmpty()) {
            $this->command->info('No client users found to write reviews.');
            return;
        }

        foreach ($properties as $property) {
            // Create 1-5 reviews per property
            $reviewCount = rand(1, 5);

            for ($i = 0; $i < $reviewCount; $i++) {
                $client = $clients->random();
                $property->rate(rand(3, 5), null, $client->id);

                // Check if this user already reviewed this property to avoid duplicates if necessary
                // But for seeding, simple approach is fine, or firstOrCreate
                PropertyReview::firstOrCreate([
                    'user_id' => $client->id,
                    'property_id' => $property->id,
                ], [
                    'rating' => rand(3, 5), // Mostly positive for demo
                    'review' => $this->getRandomReview($faker),
                    'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                ]);
            }
        }
    }

    private function getRandomReview($faker)
    {
        $reviews = [
            "Great location and very clean. Would stay again!",
            "The host was amazing and very helpful.",
            "A bit smaller than expected, but cozy.",
            "Perfect for a weekend getaway.",
            "Stunning views and close to everything.",
            "Clean, comfortable, and well-equipped.",
            "Had a wonderful time here. Highly recommended.",
            "Good value for money.",
            "The place was exactly as described.",
            "Lovely apartment with great amenities."
        ];

        return $faker->randomElement($reviews) . " " . $faker->sentence;
    }
}
