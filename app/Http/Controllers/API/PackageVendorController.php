<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PackageTypePricing;
use App\Models\Vendor;
use Illuminate\Http\Request;


class PackageVendorController extends Controller
{

    //
    public function areaOfOperations(Request $request, $id)
    {
        $vendor = Vendor::find($id);
        $cities = $vendor->cities->pluck("name");
        $states = $vendor->states->pluck("name");
        $countries = $vendor->countries->pluck("name");
        return response()->json([
            "cities" => $cities,
            "states" => $states,
            "countries" => $countries,
        ], 200);
    }

    public function vendorPricing(Request $request, $id)
    {
        return PackageTypePricing::where('vendor_id', $id)->active()->get();
    }
}