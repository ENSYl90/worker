<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BrandsTableSeeder extends Seeder
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
        \DB::table('brands')->delete();

        // Insert data
        \DB::table('brands')->insert([
            // Food & Beverage Brands
            [
                'id' => 1,
                'name' => 'Coca-Cola',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Pepsi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Nestle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Unilever',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Cadbury',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'Heinz',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'name' => "Kellogg's",
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'General Mills',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'name' => 'Starbucks',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'name' => 'Red Bull',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Electronics & Technology Brands
            [
                'id' => 11,
                'name' => 'Apple',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'name' => 'Samsung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
                'name' => 'Sony',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'name' => 'LG',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 15,
                'name' => 'HP',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16,
                'name' => 'Dell',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 17,
                'name' => 'Lenovo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 18,
                'name' => 'Microsoft',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 19,
                'name' => 'Canon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 20,
                'name' => 'Nikon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Fashion & Apparel Brands
            [
                'id' => 21,
                'name' => 'Nike',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 22,
                'name' => 'Adidas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 23,
                'name' => 'Puma',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 24,
                'name' => 'Zara',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 25,
                'name' => 'H&M',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 26,
                'name' => 'Gucci',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 27,
                'name' => 'Louis Vuitton',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 28,
                'name' => 'Levi\'s',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Beauty & Personal Care Brands
            [
                'id' => 29,
                'name' => 'L\'Oreal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 30,
                'name' => 'Nivea',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 31,
                'name' => 'Dove',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 32,
                'name' => 'Gillette',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 33,
                'name' => 'Colgate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 34,
                'name' => 'Oral-B',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Home & Kitchen Brands
            [
                'id' => 35,
                'name' => 'Ikea',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 36,
                'name' => 'Philips',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 37,
                'name' => 'Bosch',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 38,
                'name' => 'Panasonic',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 39,
                'name' => 'Dyson',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 40,
                'name' => 'KitchenAid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Automotive Brands
            [
                'id' => 41,
                'name' => 'Toyota',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 42,
                'name' => 'Honda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 43,
                'name' => 'BMW',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 44,
                'name' => 'Mercedes-Benz',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 45,
                'name' => 'Ford',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Sports & Outdoor Brands
            [
                'id' => 46,
                'name' => 'Under Armour',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 47,
                'name' => 'Reebok',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 48,
                'name' => 'New Balance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 49,
                'name' => 'The North Face',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 50,
                'name' => 'Columbia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Baby & Kids Brands
            [
                'id' => 51,
                'name' => 'Pampers',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 52,
                'name' => 'Huggies',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 53,
                'name' => 'Fisher-Price',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 54,
                'name' => 'LEGO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Health & Wellness Brands
            [
                'id' => 55,
                'name' => 'Johnson & Johnson',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 56,
                'name' => 'Pfizer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 57,
                'name' => 'GNC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 58,
                'name' => 'Centrum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Pet Care Brands
            [
                'id' => 59,
                'name' => 'Pedigree',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 60,
                'name' => 'Whiskas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 61,
                'name' => 'Purina',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Additional Popular Brands
            [
                'id' => 62,
                'name' => 'Amazon Basics',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 63,
                'name' => 'Generic',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 64,
                'name' => 'No Brand',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 65,
                'name' => 'Local Brand',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
