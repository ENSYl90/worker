<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Hotels', 'icon' => 'hotel-01'],
            ['name' => 'Apartments', 'icon' => 'building-03'],
            ['name' => 'Resorts', 'icon' => 'beach-02'],
            ['name' => 'Villas', 'icon' => 'home-06'],
            ['name' => 'Hostels', 'icon' => 'bed-double'],
            ['name' => 'Guest Houses', 'icon' => 'home-05'],
            ['name' => 'Bed & Breakfasts', 'icon' => 'restaurant-01'],
            ['name' => 'Holiday Homes', 'icon' => 'home-04'],
            ['name' => 'Cottages', 'icon' => 'home-02'],
            ['name' => 'Motels', 'icon' => 'hotel-01'],
        ];

        foreach ($types as $typeData) {
            $propertyType = PropertyType::updateOrCreate(
                ['slug' => Str::slug($typeData['name'])],
                [
                    'name' => $typeData['name'],
                    'is_active' => true,
                ]
            );

            // Download and attach icon
            $success = attachHugeiconToModel($propertyType, $typeData['icon']);
            if ($success) {
                $this->command->info("    ✓ Icon downloaded for Property Type: {$typeData['name']} ({$typeData['icon']})");
            } else {
                $this->command->warn("    ✗ Failed to download icon for Property Type: {$typeData['name']} ({$typeData['icon']})");
            }
        }

        $this->command->info('Property types seeded successfully!');
    }
}
