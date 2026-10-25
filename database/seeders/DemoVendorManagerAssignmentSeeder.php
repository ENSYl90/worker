<?php

namespace Database\Seeders;


use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorManager;
use App\Models\VendorType;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoVendorManagerAssignmentSeeder extends Seeder
{


    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //regular managers
        $regularManagers = User::where("email", "manager@demo.com")->get();
        $regularVendorTypeIds = VendorType::whereNotIn("slug", ["parcel", "service", "taxi", "booking"])->pluck("id")->toArray();
        $regularVendors = Vendor::whereIn("vendor_type_id", $regularVendorTypeIds)->get();
        $this->assignManagers($regularManagers, $regularVendors);

        //package
        $packageManagers = User::where("email", "manager1@demo.com")->get();
        $parcelVendorType = VendorType::where("slug", "parcel")->first();
        $packageVendors = Vendor::where("vendor_type_id", $parcelVendorType->id)->get();
        $this->assignManagers($packageManagers, $packageVendors);

        //service managers
        $serviceManagers = User::where("email", "manager2@demo.com")->get();
        $serviceBookingVendorTypeIds = VendorType::whereIn("slug", ["service", "booking"])->pluck("id")->toArray();
        $serviceVendors = Vendor::whereIn("vendor_type_id", $serviceBookingVendorTypeIds)->get();
        $this->assignManagers($serviceManagers, $serviceVendors);
    }


    public function assignManagers($managers, $vendors)
    {

        try {

            DB::beginTransaction();

            foreach ($vendors as $vendor) {

                //remove all managers
                User::where('vendor_id', $vendor->id)
                    ->update(['vendor_id' => null]);

                //clear old vendor manager record
                VendorManager::where('vendor_id', $vendor->id)->delete();

                //assigning
                foreach ($managers as $manager) {
                    $manager->vendor_id = $vendor->id;
                    $manager->save();
                    //
                    VendorManager::firstOrCreate([
                        "vendor_id" => $vendor->id,
                        "user_id" => $manager->id,
                    ], []);
                }
            }


            DB::commit();
        } catch (Exception $error) {
            DB::rollback();
        }
    }
}
