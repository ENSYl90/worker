<?php

namespace Database\Seeders;

use App\Models\PackageType;
use Illuminate\Database\Seeder;

class PackageTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('package_types')->delete();

        $names = [
            "Small Package (<5kg)",
            "Medium Package (<20kg)",
            "Large Package (20-50kg)",
            "XLarge Package (50kg-1ton)",
        ];
        $descriptions = [
            "Package from 0 - 5kg in weight. Example: Food, Medication, Documents etc.",
            "Package of 20kg max in weight.",
            "Package from 20 - 50kg in weight.",
            "For order involing packages weighing up to 1 ton. Example: House moving, Delivery Warehouse, Bulk Delivery etc.",
        ];

        foreach ($names as $key => $name) {
            $packageType = new PackageType();
            $packageType->name = $name;
            $packageType->description = $descriptions[$key];
            $packageType->save();
            $packageType->clearMediaCollection();
            //
            $index = $key + 1;
            $packageTypeImage = public_path("images/vendor/modules/parcel/types/{$index}.png");
            $packageType->addMedia($packageTypeImage)
                ->preservingOriginal()
                ->toMediaCollection();
        }
    }
}
