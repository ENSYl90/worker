<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file / Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        \DB::table('tags')->delete();

        // Insert data
        \DB::table('tags')->insert([
            // General/Common Tags
            [
                'id' => 1,
                'name' => '{"en":"Popular"}',
                'vendor_type_id' => null,
                'in_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '{"en":"Featured"}',
                'vendor_type_id' => null,
                'in_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => '{"en":"New"}',
                'vendor_type_id' => null,
                'in_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => '{"en":"Trending"}',
                'vendor_type_id' => null,
                'in_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => '{"en":"Best Seller"}',
                'vendor_type_id' => null,
                'in_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Food & Restaurant Tags
            [
                'id' => 6,
                'name' => '{"en":"Spicy"}',
                'vendor_type_id' => null,
                'in_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => '{"en":"Vegetarian"}',
                'vendor_type_id' => null,
                'in_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => '{"en":"Vegan"}',
                'vendor_type_id' => null,
                'in_order' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => '{"en":"Gluten Free"}',
                'vendor_type_id' => null,
                'in_order' => 9,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => '{"en":"Organic"}',
                'vendor_type_id' => null,
                'in_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'name' => '{"en":"Healthy"}',
                'vendor_type_id' => null,
                'in_order' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'name' => '{"en":"Low Carb"}',
                'vendor_type_id' => null,
                'in_order' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Dietary & Allergen Tags
            [
                'id' => 13,
                'name' => '{"en":"Dairy Free"}',
                'vendor_type_id' => null,
                'in_order' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'name' => '{"en":"Nut Free"}',
                'vendor_type_id' => null,
                'in_order' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 15,
                'name' => '{"en":"Halal"}',
                'vendor_type_id' => null,
                'in_order' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16,
                'name' => '{"en":"Kosher"}',
                'vendor_type_id' => null,
                'in_order' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Preparation & Quality Tags
            [
                'id' => 17,
                'name' => '{"en":"Fresh"}',
                'vendor_type_id' => null,
                'in_order' => 17,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 18,
                'name' => '{"en":"Homemade"}',
                'vendor_type_id' => null,
                'in_order' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 19,
                'name' => '{"en":"Premium"}',
                'vendor_type_id' => null,
                'in_order' => 19,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 20,
                'name' => '{"en":"Chef Special"}',
                'vendor_type_id' => null,
                'in_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Size & Portion Tags
            [
                'id' => 21,
                'name' => '{"en":"Large Portion"}',
                'vendor_type_id' => null,
                'in_order' => 21,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 22,
                'name' => '{"en":"Family Size"}',
                'vendor_type_id' => null,
                'in_order' => 22,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Promotion & Discount Tags
            [
                'id' => 23,
                'name' => '{"en":"On Sale"}',
                'vendor_type_id' => null,
                'in_order' => 23,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 24,
                'name' => '{"en":"Limited Time"}',
                'vendor_type_id' => null,
                'in_order' => 24,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 25,
                'name' => '{"en":"Special Offer"}',
                'vendor_type_id' => null,
                'in_order' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Service & Delivery Tags
            [
                'id' => 26,
                'name' => '{"en":"Fast Delivery"}',
                'vendor_type_id' => null,
                'in_order' => 26,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 27,
                'name' => '{"en":"Free Delivery"}',
                'vendor_type_id' => null,
                'in_order' => 27,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Additional Food Tags
            [
                'id' => 28,
                'name' => '{"en":"Breakfast"}',
                'vendor_type_id' => null,
                'in_order' => 28,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 29,
                'name' => '{"en":"Lunch"}',
                'vendor_type_id' => null,
                'in_order' => 29,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 30,
                'name' => '{"en":"Dinner"}',
                'vendor_type_id' => null,
                'in_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
