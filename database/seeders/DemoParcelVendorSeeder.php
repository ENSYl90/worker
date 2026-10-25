<?php

namespace Database\Seeders;

use App\Models\CountryVendor;
use App\Models\Country;
use App\Models\PackageType;
use App\Models\PackageTypePricing;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Traits\ImageGeneratorTrait;
use Illuminate\Database\Seeder;

class DemoParcelVendorSeeder extends Seeder
{

    use ImageGeneratorTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //create package types
        $names = [
            "Small Package (<5kg)",
            "Medium Package (<20kg)",
            "Large Package (20-50kg)",
            "XLarge Package (50kg-1ton)",
        ];
        $descriptions = [
            "Lightweight deliveries such as documents, medication, small food parcels, and personal items up to 5kg.",
            "Mid-size parcels and boxed items up to 20kg, ideal for local retail and office deliveries.",
            "Bulky packages from 20kg to 50kg that need extra handling and vehicle space.",
            "Large moves and heavy freight up to 1 ton, including warehouse dispatches and household items.",
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
        //
        $parcelVendorTypeId = VendorType::where('slug', 'parcel')->first()->id;
        Vendor::where('vendor_type_id', $parcelVendorTypeId)->delete();
        //
        $vendorNames = ['Arrowline Couriers', 'SwiftParcel Express', 'GoPack Logistics', 'PrimeRoute Solutions'];
        $vendorDecriptions = [
            "Last-mile courier service for documents, retail orders, and urgent local deliveries with practical tracking support.",
            "Express parcel delivery for customers who need reliable same-day and next-day shipping options.",
            "Flexible logistics for individuals and small businesses, from single parcels to recurring delivery runs.",
            "Route-focused delivery support with clear pricing, scheduled pickups, and dependable package handling."
        ];
        //
        $countries = Country::get()->pluck("id")->toArray();
        $faker = \Faker\Factory::create();
        //Loop through the vendor names
        foreach ($vendorNames as $key => $vendorName) {
            $model = new Vendor();
            $model->name = $vendorName;
            $model->description = $vendorDecriptions[$key];
            $model->delivery_fee = $faker->randomNumber(2, false);
            $model->delivery_range = 40075;
            $model->tax = rand(0, 50);
            $model->phone = preg_replace('/[^0-9+]/', '', $faker->phoneNumber);
            $model->email = $faker->email;
            $model->address = $faker->address;
            $model->latitude = $faker->latitude();
            $model->longitude = $faker->longitude();
            $model->tax = rand(0, 1);
            $model->pickup = 0;
            $model->delivery = 0;
            $model->is_active = 1;
            $model->vendor_type_id = $parcelVendorTypeId;
            $model->saveQuietly();
            //logo image
            try {
                //logo
                $model->clearMediaCollection();
                $index = $key + 1;
                $logoImage = public_path("images/vendor/modules/parcel/{$index}.jpg");
                $model->addMedia($logoImage)
                    ->preservingOriginal()
                    ->toMediaCollection("logo");

                //keep the original image
                $featureImage = public_path('images/vendor/modules/parcel/feature-img.jpg');
                $model->addMedia($featureImage)
                    ->preservingOriginal()
                    ->toMediaCollection("feature_image");
            } catch (\Exception $ex) {
                logger("Error", [$ex->getMessage()]);
            }
            //add pricing
            $packageTypes = PackageType::get();
            foreach ($packageTypes as $key => $packageType) {
                $packageTypePricing = new PackageTypePricing();
                $packageTypePricing->vendor_id = $model->id;
                $packageTypePricing->package_type_id = $packageType->id;
                $packageTypePricing->max_booking_days = rand(1, 21);
                $packageTypePricing->size_price = rand(1, 50);
                $packageTypePricing->price_per_kg = rand(0, 1);
                $packageTypePricing->distance_price = rand(1, 50);
                $packageTypePricing->base_price = rand(1, 50);
                $packageTypePricing->multiple_stop_fee = rand(1, 50);
                $packageTypePricing->price_per_km = rand(0, 1);
                $packageTypePricing->is_active = 1;
                $packageTypePricing->auto_assignment = rand(0, 1);
                $packageTypePricing->field_required = rand(0, 1);
                $packageTypePricing->save();
            }

            //add all countries of operations
            foreach ($countries as $countryId) {
                $countryVendor = new CountryVendor();
                $countryVendor->country_id = $countryId;
                $countryVendor->vendor_id = $model->id;
                $countryVendor->is_active = 1;
                $countryVendor->save();
            }
        }
    }
}
