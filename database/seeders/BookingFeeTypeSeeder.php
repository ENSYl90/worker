<?php

namespace Database\Seeders;

use App\Models\BookingFeeType;
use Illuminate\Database\Seeder;

class BookingFeeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key check for this operation
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate existing records
        BookingFeeType::truncate();
        
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $feeTypes = [
            [
                'name' => 'Cleaning Fee',
                'description' => 'One-time fee for cleaning the property after stay',
                'calculation_type' => BookingFeeType::CALCULATION_FIXED,
                'is_mandatory' => true,
                'is_taxable' => true,
                'display_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Service Fee',
                'description' => 'Platform service fee',
                'calculation_type' => BookingFeeType::CALCULATION_PERCENTAGE,
                'is_mandatory' => true,
                'is_taxable' => true,
                'display_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Resort Fee',
                'description' => 'Daily fee for resort amenities',
                'calculation_type' => BookingFeeType::CALCULATION_PER_NIGHT,
                'is_mandatory' => true,
                'is_taxable' => true,
                'display_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Extra Guest Fee',
                'description' => 'Fee for each additional guest',
                'calculation_type' => BookingFeeType::CALCULATION_PER_GUEST,
                'is_mandatory' => false,
                'is_taxable' => false,
                'display_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Pet Fee',
                'description' => 'One-time fee for bringing pets',
                'calculation_type' => BookingFeeType::CALCULATION_FIXED,
                'is_mandatory' => false,
                'is_taxable' => true,
                'display_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($feeTypes as $feeType) {
            $feeType['slug'] = \Illuminate\Support\Str::slug($feeType['name']);
            BookingFeeType::create($feeType);
        }

        $this->command->info('Booking fee types seeded successfully!');
    }
}
