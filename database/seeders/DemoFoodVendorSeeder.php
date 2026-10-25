<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Support\DemoCatalog;
use App\Traits\ImageGeneratorTrait;
use App\Traits\MediaModelConnectorTrait;
use Illuminate\Database\Seeder;

class DemoFoodVendorSeeder extends Seeder
{

    use ImageGeneratorTrait, MediaModelConnectorTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $foodVendorTypeId = VendorType::where('slug', 'food')->first()->id;
        $vendorIds = Vendor::where('vendor_type_id', $foodVendorTypeId)->pluck('id')->toArray();
        Product::whereIn('vendor_id', $vendorIds)->delete();
        Vendor::where('vendor_type_id', $foodVendorTypeId)->delete();


        //
        $vendors = DemoCatalog::vendorsFor('food');
        $faker = \Faker\Factory::create();
        foreach ($vendors as $vendorData) {
            $model = new Vendor();
            $model->name = $vendorData['name'];
            $model->description = $vendorData['description'];
            $model->delivery_fee = $faker->randomNumber(2, false);
            $model->delivery_range = 40075;
            $model->tax = rand(0, 50);
            $model->phone = preg_replace('/[^0-9+]/', '', $faker->phoneNumber);
            $model->email = $faker->email;
            $model->address = $faker->address;
            $model->latitude = $faker->latitude();
            $model->longitude = $faker->longitude();
            $model->tax = rand(0, 1);
            $model->pickup = 1;
            $model->delivery = 1;
            $model->is_active = 1;
            $model->vendor_type_id = $foodVendorTypeId;
            $model->saveQuietly();
            //logo image
            try {
                $this->setVendorImages($model);
            } catch (\Exception $ex) {
                logger("Error", [$ex->getMessage()]);
            }
            //add food
            foreach ($vendorData['products'] as $productData) {
                $food = new Product();
                $food->name = $productData['name'];
                $food->description = $productData['description'];
                $food->price = rand(1, 50);
                $food->is_active = 1;
                $food->deliverable = rand(0, 1);
                $food->featured = rand(0, 1);
                $food->vendor_id = $model->id;
                $food->saveQuietly();
                $food->approved = true;
                $food->saveQuietly();
                //
                try {
                    $this->setProductImages($food);
                } catch (\Exception $ex) {
                    logger("Error", [$ex->getMessage()]);
                }
            }
        }
    }
}
