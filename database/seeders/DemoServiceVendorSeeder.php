<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use App\Models\Subcategory;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Support\DemoCatalog;
use App\Traits\ImageGeneratorTrait;
use App\Traits\MediaModelConnectorTrait;
use Illuminate\Database\Seeder;

class DemoServiceVendorSeeder extends Seeder
{

    use ImageGeneratorTrait, MediaModelConnectorTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //
        $vendorTypeId = VendorType::where('slug',  'service')->first()->id;

        //handle service categories generation
        $this->generateServiceCategories();
        //cretae category
        $category = Category::firstOrCreate([
            'vendor_type_id' => $vendorTypeId,
        ], [
            'name' => 'General',
            'is_active' => 1,
        ]);

        //create subcategory
        $subcategory = Subcategory::firstOrCreate([
            'category_id' => $category->id,
        ], [
            'name' => 'General',
            'is_active' => 1,
        ]);

        //delete all vendors with food type
        // $vendorIds = Vendor::where('vendor_type_id', $vendorTypeId)->pluck('id')->toArray();
        // Vendor::where('vendor_type_id', $vendorTypeId)->delete();
        //delete all products
        // Service::whereIn('vendor_id', $vendorIds)->delete();


        //
        $faker = \Faker\Factory::create();
        $vendors = DemoCatalog::vendorsFor('service');
        foreach ($vendors as $vendorData) {
            $model = new Vendor();
            $model->name = $vendorData['name'];
            $model->description = $vendorData['description'];
            $model->delivery_fee = rand(5, 60);
            $model->delivery_range = 99999999;
            $phoneNumber = $faker->phoneNumber;
            $phoneNumber = preg_replace('/[^\d+]/', '', $phoneNumber);
            $phoneNumber = str_replace(" ", "", $phoneNumber);
            $model->phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
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
            foreach ($vendorData['services'] as $vendorServiceData) {
                $service = new Service();
                $service->name = $vendorServiceData['name'];
                $service->description = $vendorServiceData['description'];
                $service->price = rand(1, 50);
                $service->is_active = 1;
                $service->duration = "fixed";
                $service->vendor_id = $model->id;
                $service->category_id = $category->id;
                $service->subcategory_id = $subcategory->id;
                $service->saveQuietly();
                //
                try {
                    $this->setProductImages($service);
                } catch (\Exception $ex) {
                    logger("Error", [$ex->getMessage()]);
                }
            }
        }
    }


    public function generateServiceCategories()
    {
        //
    }
}
