<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\OrderStop;
use App\Models\PackageTypePricing;
use App\Models\Vendor;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Traits\GeoBoundaryCheckTrait;
use App\Traits\GoogleMapApiTrait;
use App\Traits\VendorLocationCheckTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PackageOrderController extends Controller
{
    use GoogleMapApiTrait;
    use GeoBoundaryCheckTrait;
    use VendorLocationCheckTrait;

    public function fetchVendors(Request $request)
    {
        try {
            $vendors = Vendor::with('package_types_pricing')->where('vendor_type_id', $request->vendor_type_id)
                ->whereHas('package_types_pricing', function ($query) use ($request) {
                    $query->where('package_type_id', $request->package_type_id);
                })
                ->active()
                ->get();


            $vendorsWithAllLocations = [];
            $vendorsWithAtLeastOneLocation = [];

            foreach ($vendors as $vendor) {
                $vendorServiceAllLocations = true;
                $unSupportedLocations = [];
                foreach ($request->locations as $location) {
                    if (!$this->isStopServiceByVendor($vendor->id, $location)) {
                        $vendorServiceAllLocations = false;
                        $unSupportedLocations[] = $location;
                        break;
                    }
                }

                if ($vendorServiceAllLocations) {
                    $vendorsWithAllLocations[] = $vendor->toArray();
                } else {
                    $vendor['unsupported_locations'] = $unSupportedLocations;
                    $vendorsWithAtLeastOneLocation[] = $vendor->toArray();
                }
            }


            if (!$request->has('correct_only') || $request->correct_only == true) {
                $vendorsWithAllLocations = collect($vendorsWithAllLocations)->map(function ($vendor) {
                    // unset($vendor['package_types_pricing'], $vendor['cities']);
                    unset($vendor['cities']);
                    unset($vendor['countries'], $vendor['states']);
                    return $vendor;
                });

                return response()->json([
                    "vendors" => $vendorsWithAllLocations,
                ], 200);
            }


            $vendorsWithAllLocations = collect($vendorsWithAllLocations)->map(function ($vendor) {
                return new Vendor($vendor);
            });
            $vendorsWithAtLeastOneLocation = collect($vendorsWithAtLeastOneLocation)->map(function ($vendor) {
                return new Vendor($vendor);
            });

            $allVendors = $vendorsWithAllLocations->merge($vendorsWithAtLeastOneLocation);
            $allVendors = $allVendors->toArray();

            return response()->json([
                "vendors" => $allVendors,
            ], 200);
        } catch (\Exception $ex) {
            logger("fetch vendors fail", [$ex]);
            return response()->json([
                "message" => $ex->getMessage(),
            ], 400);
        }
    }

    public function summary(Request $request)
    {
        try {
            $ignoreCheck = (bool) $request->ignore_check ?? false;
            if (!$ignoreCheck && !empty($request->pickup_location_id) || !empty($request->dropoff_location_id)) {
                if (!$this->isLocationAllowedByVendor($request->vendor_id, $request->pickup_location_id)) {
                    return response()->json([
                        "message" => __("Vendor does not service pickup location"),
                    ], 400);
                } else if (!$this->isLocationAllowedByVendor($request->vendor_id, $request->dropoff_location_id)) {
                    return response()->json([
                        "message" => __("Vendor does not service drop-off location"),
                    ], 400);
                }
            }

            $deliveryLocationDistance = 0;
            $originLatLng = "";
            $destinationLatLngs = "";
            $packageTypePricing = PackageTypePricing::where('vendor_id', $request->vendor_id)
                ->where('package_type_id', $request->package_type_id)->first();


            if (!empty($request->stops)) {
                $stops = $request->stops;
                try {
                    $totalStops = count($stops);
                } catch (\Exception $ex) {
                    $stops = json_decode($request->stops, true);
                    $request->merge(["stops" => $stops]);
                    $totalStops = count($stops);
                }
                $newTotalStops = $totalStops - 1;

                for ($i = 0; $i < $newTotalStops; $i++) {
                    $stop = $stops[$i];
                    $nextStop = $stops[$i + 1];
                    if (!$ignoreCheck && !$this->isLocationAllowedByVendor($request->vendor_id, $stop)) {
                        return response()->json([
                            "message" => __("Vendor does not service stop location"),
                        ], 400);
                    }

                    $stopTripDistance = $this->getDistanceBetweenStop($stop, $nextStop);
                    $deliveryLocationDistance += $stopTripDistance;
                    if ($packageTypePricing->price_per_km) {
                        $stopTripPrice = $packageTypePricing->distance_price * $stopTripDistance;
                    } else {
                        $stopTripPrice = $packageTypePricing->distance_price;
                    }
                    $stops[$i + 1]["price"] = $stopTripPrice;
                }
            } else {
                $dropoffLocation = $this->getDeliveryAddress($request->dropoff_location_id);
                $deliveryLocationDistance = DeliveryAddress::distance($dropoffLocation->latitude, $dropoffLocation->longitude)
                    ->where('id', $request->pickup_location_id)
                    ->first()
                    ->distance;
            }


            if (setting('enableGoogleDistance', 0)) {
                $deliveryLocationDistance = 0;
                if (!empty($request->stops)) {

                    $stops = $request->stops;
                    $totalStops = count($stops);
                    $newTotalStops = $totalStops - 1;

                    for ($i = 0; $i < $newTotalStops; $i++) {
                        $stop = $stops[$i];
                        $nextStop = $stops[$i + 1];
                        $stopId = $stop["id"] ?? null;
                        $nextStopId = $nextStop["id"] ?? null;
                        if ($stopId == null || empty($stopId) || $nextStopId == null || empty($nextStopId)) {
                            $originLatLng = "{$stop['lat']},{$stop['lng']}";
                            $destinationLatLngs = "{$nextStop['lat']},{$nextStop['lng']}";
                        } else {
                            $originAddress = $this->getDeliveryAddress($stop["id"]);
                            $destinationAddress = $this->getDeliveryAddress($nextStop["id"]);
                            $originLatLng = "" . $originAddress->latitude . "," . $originAddress->longitude;
                            $destinationLatLngs = "" . $destinationAddress->latitude . "," . $destinationAddress->longitude;
                        }
                        $stopTripDistance = $this->getTotalDistanceFromGoogle(
                            $originLatLng,
                            $destinationLatLngs
                        );
                        $deliveryLocationDistance += $stopTripDistance;
                        if ($packageTypePricing->price_per_km) {
                            $stopTripPrice = $packageTypePricing->distance_price * $stopTripDistance;
                        } else {
                            $stopTripPrice = $packageTypePricing->distance_price;
                        }
                        $stops[$i + 1]["price"] = $stopTripPrice;
                    }
                } else {
                    $pickupLocation = $this->getDeliveryAddress($request->pickup_location_id);
                    $dropoffLocation = $this->getDeliveryAddress($request->dropoff_location_id);
                    $originLatLng = "" . $pickupLocation->latitude . "," . $pickupLocation->longitude;
                    $destinationLatLngs = "" . $dropoffLocation->latitude . "," . $dropoffLocation->longitude;
                    $deliveryLocationDistance = $this->getTotalDistanceFromGoogle(
                        $originLatLng,
                        $destinationLatLngs
                    );
                }
            }
            $tax = Vendor::find($request->vendor_id)->tax;
            $tax ??= setting('finance.generalTax', 0);
            $sizeAmount = 0;
            $distanceAmount = 0;
            $totalAmount = 0;
            if ($packageTypePricing->price_per_kg) {
                $sizeAmount = $packageTypePricing->size_price * $request->weight;
            } else {
                $sizeAmount = $packageTypePricing->size_price;
            }
            if ($packageTypePricing->price_per_km) {
                $distanceAmount = $packageTypePricing->distance_price * $deliveryLocationDistance;
            } else {
                $distanceAmount = $packageTypePricing->distance_price;
            }
            $distanceAmount += $packageTypePricing->base_price;
            if (!empty($request->stops) && count($request->stops) > 2) {
                $distanceAmount += ($packageTypePricing->multiple_stop_fee) * count($request->stops);
            }

            $subTotalAmount = floatval($distanceAmount + $sizeAmount);
            $discount = 0;
            $coupon = null;
            if (!empty($request->coupon_code)) {
                $vendor = Vendor::find($request->vendor_id);
                $coupon = Coupon::where('code', $request->coupon_code)->first();

                if ($coupon == null) {
                    throw new \Exception(__("Coupon code is invalid"));
                }
                if ($coupon->vendor_type_id != null && $coupon->vendor_type_id != $vendor->vendor_type_id) {
                    throw new \Exception(__("Coupon code is invalid for this vendor type"));
                }

                $usedTimes = CouponUser::where('coupon_id', $coupon->id)
                    ->where('user_id', Auth::id())
                    ->count() ?? 0;
                if ($coupon->times != null && $usedTimes >= $coupon->times) {
                    throw new \Exception(__("You have exceeded number of use"), 1);
                }
                $vendorIds = $coupon->vendors->pluck('id')->toArray();
                if (count($vendorIds) > 0 && !in_array($vendor->id, $vendorIds)) {
                    throw new \Exception(__("Coupon code is invalid for this vendor"));
                }

                if ($coupon) {
                    if ($coupon->percentage) {
                        $discount = floatval(($coupon->discount / 100)) * floatval($subTotalAmount);
                    } else {
                        $discount = $coupon->discount;
                    }
                    if ($coupon->max_coupon_amount != null && $discount > $coupon->max_coupon_amount) {
                        $discount = $coupon->max_coupon_amount;
                    }
                    if ($coupon->min_order_amount != null && $subTotalAmount < $coupon->min_order_amount) {
                        $discount = 0;
                    }
                }
            }

            $tax = floatval($tax);
            $discount = floatval($discount);
            $taxAmount = ($tax / 100) * $subTotalAmount;
            $taxAmount = floatval($taxAmount);
            $totalAmount = $taxAmount + ($subTotalAmount - $discount);
            $vendor = Vendor::find($request->vendor_id);
            $totalFee = 0;
            $vendorFees = [];

            foreach ($vendor->fees as $fee) {
                $feeAmount = 0;
                if ($fee->percentage) {
                    $totalFee += $feeAmount = ($fee->value / 100) * $subTotalAmount;
                } else {
                    $totalFee += $feeAmount = $fee->value;
                }
                $vendorFees[] = [
                    "id" => $fee->id,
                    "name" => $fee->name,
                    "value" => $fee->value,
                    "percentage" => $fee->percentage,
                    "amount" => $feeAmount,
                ];
            }
            $totalAmount += $totalFee;
            $stops ??= [];
            foreach ($stops as $key => &$stopitem) {
                if (!is_null($stopitem['id'])) {
                    $stops[$key]['id'] = ((int) $stopitem['id']) ?? null;
                }
            }

            $result = [
                "delivery_fee" => $distanceAmount,
                "package_type_fee" => $sizeAmount,
                "distance" => $deliveryLocationDistance,
                "sub_total" => $subTotalAmount,
                "discount" => $discount,
                "tax" => (float) $taxAmount,
                "tax_rate" => (float) $tax,
                "fees" => (float) $totalFee,
                "vendor_fees" => $vendorFees,
                "total" => $totalAmount,
                "coupon" => $coupon ?? null,
                "stops" => $stops ?? [],
            ];
            $token = \Crypt::encrypt($result);
            $result["token"] = $token;


            return response()->json($result);
        } catch (\Exception $ex) {
            logger("Order calculation error", [$ex]);
            return response()->json([
                "message" => $ex->getMessage() ?? __("Order calculation error"),
            ], 400);
        }
    }


    public function verifyOrderStop(Request $request, $id)
    {
        $orderStop = OrderStop::find($id);
        if (empty($orderStop)) {
            return response()->json([
                "message" => __("Invalid order stop"),
            ], 400);
        }

        try {

            \DB::beginTransaction();
            $orderStop->verified = true;
            $orderStop->save();
            if ($request->hasFile("signature")) {
                $orderStop->addMedia($request->signature->getRealPath())->toMediaCollection("proof");
            }

            \DB::commit();

            return response()->json([
                "message" => __("Order stop verified"),
                "order" => Order::fullData()->where('id', $orderStop->order_id)->first(),
            ], 200);
        } catch (\Exception $ex) {
            \DB::rollback();
            logger("Order stop verification error", [$ex]);
            return response()->json([
                "message" => __("Error verifying order stop"),
            ], 400);
        }
    }


    public function getDeliveryAddress($id): ?DeliveryAddress
    {
        return DeliveryAddress::find($id);
    }

    public function getDistanceBetweenStop($stop, $nextStop)
    {
        $stopId = $stop["id"] ?? null;
        $nextStopId = $nextStop["id"] ?? null;

        if ($stopId == null || empty($stopId) || $nextStopId == null || empty($nextStopId)) {
            $originLat = $stop['lat'] ?? $stop['latitude'];
            $originLng = $stop['lng'] ?? $stop['long'] ?? $stop['longitude'];
            $destinationLat = $nextStop['lat'] ?? $nextStop['latitude'];
            $destinationLng = $nextStop['lng'] ?? $nextStop['long'] ?? $nextStop['longitude'];

            $originLatLng = "{$originLat},{$originLng}";
            $destinationLatLng = "{$destinationLat},{$destinationLng}";
            return $this->getLinearDistance($originLatLng, $destinationLatLng);
        }
        $nextLocation = $this->getDeliveryAddress($nextStopId);
        return DeliveryAddress::distance($nextLocation->latitude, $nextLocation->longitude)
            ->where('id', $stopId)
            ->first()
            ->distance;
    }
}
