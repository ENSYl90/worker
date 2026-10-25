<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PackageTypePricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorPackageTypePricingController extends Controller
{

    public function index()
    {
        $pricings = PackageTypePricing::where('vendor_id', Auth::user()->vendor_id)
            //remove pricing with trashed package types
            ->whereHas('package_type', function ($query) {
               return $query->whereNull('deleted_at');
            })
            ->when(request()->has('package_type_id'), function ($query) {
                return $query->where('package_type_id', request()->package_type_id);
            })
            //get or pagninate based on request
            ->when(request()->has('page'), function ($query) {
                return $query->paginate();
            }, function ($query) {
                return  $query->get();
            });
        return response()->json($pricings);
    }

    public function store(Request $request)
    {

        try {

            DB::beginTransaction();
            PackageTypePricing::updateOrCreate([
                "package_type_id" => $request->package_type_id,
                "vendor_id" => Auth::user()->vendor_id,
            ], $request->all());
            DB::commit();

            return response()->json([
                "message" => __("Package Pricing Set successfully"),
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                "message" => $ex->getMessage()
            ], 400);
        }
    }


    public function update(Request $request, $id)
    {
        try {

            DB::beginTransaction();
            $packageTypePricing = PackageTypePricing::find($id);
            $packageTypePricing->update($request->all());
            DB::commit();

            return response()->json([
                "message" => __("Package Pricing updated successfully"),
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                "message" => $ex->getMessage()
            ], 400);
        }
    }


    public function destroy($id)
    {
        try {

            DB::beginTransaction();
            PackageTypePricing::destroy($id);
            DB::commit();

            return response()->json([
                "message" => __("Package Pricing deleted successfully"),
            ]);
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json([
                "message" => $ex->getMessage()
            ], 400);
        }
    }
}
