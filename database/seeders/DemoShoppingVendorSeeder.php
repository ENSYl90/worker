<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Support\DemoCatalog;
use App\Traits\ImageGeneratorTrait;
use App\Traits\MediaModelConnectorTrait;
use Illuminate\Database\Seeder;

class DemoShoppingVendorSeeder extends Seeder
{
    use ImageGeneratorTrait, MediaModelConnectorTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $vendorTypeId = VendorType::where('slug', 'commerce')->first()->id;
        //delete all vendors with food type
        // $vendorIds = Vendor::where('vendor_type_id', $vendorTypeId)->pluck('id')->toArray();
        // Product::whereIn('vendor_id', $vendorIds)->delete();
        // Vendor::where('vendor_type_id', $vendorTypeId)->delete();


        $vendors = DemoCatalog::vendorsFor('commerce');
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
            $model->vendor_type_id = $vendorTypeId;
            $model->saveQuietly();
            //logo gen
            try {
                $this->setVendorImages($model);
            } catch (\Exception $ex) {
                logger("Error", [$ex->getMessage()]);
            }

            //add product
            foreach ($vendorData['products'] as $vendorProductData) {
                $product = new Product();
                $product->name = $vendorProductData['name'];
                $product->description = $vendorProductData['description'];
                $product->price = rand(1, 50);
                $product->is_active = 1;
                $product->deliverable = rand(0, 1);
                $product->featured = rand(0, 1);
                $product->vendor_id = $model->id;
                $product->saveQuietly();
                $product->approved = true;
                $product->saveQuietly();
                //
                try {
                    $this->setProductImages($product);
                } catch (\Exception $ex) {
                    logger("Error", [$ex->getMessage()]);
                }
            }
        }
    }
}
