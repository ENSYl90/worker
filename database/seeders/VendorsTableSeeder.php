<?php

namespace Database\Seeders;

use App\Models\Vendor;
use App\Models\VendorType;
use App\Traits\GeneratesNameBasedImagesTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorsTableSeeder extends Seeder
{
    use GeneratesNameBasedImagesTrait;

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('vendors')->delete();

        $faker = \Faker\Factory::create();
        $vendorTypes = VendorType::all();

        foreach ($vendorTypes as $vendorType) {
            $this->createVendorsForType($vendorType, $faker);
        }
    }

    private function createVendorsForType($vendorType, $faker)
    {
        $vendors = [];
        $deliveryRangeRange = [5, 20]; // minimal generic default
        $deliveryFeeRange = [2, 10]; // minimal generic default

        switch ($vendorType->slug) {
            case 'parcel':
                $vendors = [
                    ['name' => 'DHL Express', 'image_term' => 'delivery process'],
                    ['name' => 'FedEx', 'image_term' => 'courier'],
                    ['name' => 'UPS', 'image_term' => 'package delivery'],
                    ['name' => 'FastTrack Logistics', 'image_term' => 'logistics'],
                ];
                $deliveryRangeRange = [50, 200];
                $deliveryFeeRange = [10, 50];
                break;
            case 'food':
                $vendors = [
                    ['name' => 'Pizza Hut', 'image_term' => 'pizza'],
                    ['name' => 'Burger King', 'image_term' => 'burger'],
                    ['name' => 'Sushi Master', 'image_term' => 'sushi'],
                    ['name' => 'Taco Bell', 'image_term' => 'taco'],
                    ['name' => 'Starbucks', 'image_term' => 'coffee'],
                    ['name' => 'Dominos', 'image_term' => 'pizza'],
                    ['name' => 'KFC', 'image_term' => 'fried chicken'],
                ];
                $deliveryRangeRange = [5, 15];
                $deliveryFeeRange = [1, 5];
                break;
            case 'grocery':
                $vendors = [
                    ['name' => 'Whole Foods', 'image_term' => 'fresh vegetables'],
                    ['name' => 'Walmart', 'image_term' => 'supermarket'],
                    ['name' => '7-Eleven', 'image_term' => 'convenience store'],
                    ['name' => 'Target', 'image_term' => 'grocery store'],
                    ['name' => 'Costco', 'image_term' => 'warehouse store'],
                ];
                $deliveryRangeRange = [10, 30];
                $deliveryFeeRange = [5, 15];
                break;
            case 'pharmacy':
                $vendors = [
                    ['name' => 'CVS Pharmacy', 'image_term' => 'pharmacy'],
                    ['name' => 'Walgreens', 'image_term' => 'medicine'],
                    ['name' => 'Rite Aid', 'image_term' => 'drugstore'],
                    ['name' => 'HealthPlus', 'image_term' => 'medical supplies'],
                ];
                $deliveryRangeRange = [5, 15];
                $deliveryFeeRange = [2, 8];
                break;
            case 'service':
                $vendors = [
                    ['name' => 'Helping Hands Cleaning', 'image_term' => 'cleaning service'],
                    ['name' => 'FixIt Pro', 'image_term' => 'repair tools'],
                    ['name' => 'Sparkle Maids', 'image_term' => 'housekeeping'],
                    ['name' => 'City Plumbers', 'image_term' => 'plumbing'],
                ];
                $deliveryRangeRange = [10, 50];
                $deliveryFeeRange = [10, 30];
                break;
            case 'booking':
                $vendors = [
                    ['name' => 'Hilton Hotel', 'image_term' => 'luxury hotel'],
                    ['name' => 'Marriott', 'image_term' => 'resort'],
                    ['name' => 'Seaside Resort', 'image_term' => 'beach hotel'],
                    ['name' => 'Cozy Apartment', 'image_term' => 'modern apartment'],
                ];
                $deliveryRangeRange = [1, 10];
                $deliveryFeeRange = [0, 0];
                break;
            case 'commerce':
                $vendors = [
                    ['name' => 'Best Buy', 'image_term' => 'electronics store'],
                    ['name' => 'Zara', 'image_term' => 'clothing store'],
                    ['name' => 'Apple Store', 'image_term' => 'computer store'],
                    ['name' => 'Nike', 'image_term' => 'sportswear'],
                    ['name' => 'Amazon Hub', 'image_term' => 'shipping boxes'],
                ];
                $deliveryRangeRange = [50, 500];
                $deliveryFeeRange = [5, 20];
                break;
            default:
                // Generate some generic ones if type is unknown
                for ($i = 0; $i < 3; $i++) {
                    $vendors[] = ['name' => $faker->company, 'image_term' => 'business'];
                }
                break;
        }

        foreach ($vendors as $vendorData) {
            $model = new Vendor();
            $model->name = $vendorData['name'];
            $model->description = $faker->sentence;
            $model->delivery_fee = rand($deliveryFeeRange[0], $deliveryFeeRange[1]);
            $model->delivery_range = rand($deliveryRangeRange[0], $deliveryRangeRange[1]);
            $model->tax = rand(0, 10);
            $model->phone = $faker->phoneNumber;
            $model->email = $faker->unique()->safeEmail;
            $model->address = $faker->address;
            $model->latitude = $faker->latitude;
            $model->longitude = $faker->longitude;
            $model->pickup = 1; // Generally allow pickup
            $model->delivery = 1; // Generally allow delivery
            $model->is_active = 1;
            $model->vendor_type_id = $vendorType->id;

            $model->save();

            try {
                $images = $this->generateNameBasedImages($model->name, 'seeder-media/vendors');

                $model->addMedia($images['feature_image'])
                    ->preservingOriginal()
                    ->toMediaCollection("feature_image");

                $model->addMedia($images['logo'])
                    ->preservingOriginal()
                    ->toMediaCollection("logo");
            } catch (\Exception $ex) {
                logger("Error seeding media for vendor " . $model->name, [$ex->getMessage()]);
            }
        }
    }
}
